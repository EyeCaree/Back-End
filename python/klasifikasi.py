import torch
import torch.nn as nn
import torch.optim as optim
import torchvision.transforms as transforms
from torchvision import datasets
from torch.utils.data import DataLoader, WeightedRandomSampler
import numpy as np
from tqdm import tqdm
import onnx

# Cek GPU
device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
print(f"Using device: {device}")

# 1️⃣ **Data Augmentation & Normalization**
transform = transforms.Compose([
    transforms.Resize((224, 224)),  
    transforms.RandomHorizontalFlip(),
    transforms.RandomRotation(10),
    transforms.ColorJitter(brightness=0.2, contrast=0.2),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.5, 0.5, 0.5], std=[0.5, 0.5, 0.5])
])

# 2️⃣ **Load Dataset**
train_path = "resources/assets/train"
test_path = "resources/assets/test"

train_dataset = datasets.ImageFolder(root=train_path, transform=transform)
test_dataset = datasets.ImageFolder(root=test_path, transform=transform)

# 3️⃣ **Handling Dataset Tidak Seimbang dengan Weighted Loss**
class_counts = np.bincount([label for _, label in train_dataset.samples])
class_weights = 1.0 / torch.tensor(class_counts, dtype=torch.float)

# Buat sampler agar sampling lebih seimbang
sample_weights = [class_weights[label] for _, label in train_dataset.samples]
sampler = WeightedRandomSampler(sample_weights, num_samples=len(sample_weights), replacement=True)

train_loader = DataLoader(dataset=train_dataset, batch_size=32, sampler=sampler)
test_loader = DataLoader(dataset=test_dataset, batch_size=32, shuffle=False)

num_classes = len(train_dataset.classes)

# 4️⃣ **Model CNN yang Lebih Dalam**
class CNNModel(nn.Module):
    def __init__(self, num_classes):
        super(CNNModel, self).__init__()
        self.conv1 = nn.Conv2d(3, 32, kernel_size=3, padding=1)
        self.conv2 = nn.Conv2d(32, 64, kernel_size=3, padding=1)
        self.conv3 = nn.Conv2d(64, 128, kernel_size=3, padding=1)
        self.relu = nn.ReLU()
        self.pool = nn.MaxPool2d(kernel_size=2, stride=2)
        self.fc1 = nn.Linear(128 * 28 * 28, 512)
        self.fc2 = nn.Linear(512, num_classes)

    def forward(self, x):
        x = self.pool(self.relu(self.conv1(x)))
        x = self.pool(self.relu(self.conv2(x)))
        x = self.pool(self.relu(self.conv3(x)))
        x = x.view(x.size(0), -1)  
        x = self.relu(self.fc1(x))
        x = self.fc2(x)
        return x

# 5️⃣ **Inisialisasi Model, Loss, dan Optimizer**
model = CNNModel(num_classes=num_classes).to(device)
criterion = nn.CrossEntropyLoss(weight=class_weights.to(device))  # Menggunakan Weighted Loss
optimizer = optim.Adam(model.parameters(), lr=0.0001)

# 6️⃣ **Training & Evaluasi Model**
def train_and_evaluate(model, criterion, optimizer, train_loader, test_loader, epochs=10):
    for epoch in range(epochs):
        model.train()
        train_loss, correct_train = 0.0, 0
        total_train = 0
        
        for images, labels in tqdm(train_loader, desc=f'Training... Epoch: {epoch + 1}/{epochs}'):
            images, labels = images.to(device), labels.to(device)
            
            optimizer.zero_grad()
            outputs = model(images)
            loss = criterion(outputs, labels)
            loss.backward()
            optimizer.step()
            
            train_loss += loss.item()
            _, predicted = torch.max(outputs, 1)
            correct_train += (predicted == labels).sum().item()
            total_train += labels.size(0)

        train_accuracy = correct_train / total_train
        avg_train_loss = train_loss / len(train_loader)

        # Evaluasi
        model.eval()
        test_loss, correct_test = 0.0, 0
        total_test = 0

        with torch.no_grad():
            for images, labels in tqdm(test_loader, desc=f'Validating... Epoch: {epoch + 1}/{epochs}'):
                images, labels = images.to(device), labels.to(device)
                outputs = model(images)
                loss = criterion(outputs, labels)
                
                test_loss += loss.item()
                _, predicted = torch.max(outputs, 1)
                correct_test += (predicted == labels).sum().item()
                total_test += labels.size(0)

        test_accuracy = correct_test / total_test
        avg_test_loss = test_loss / len(test_loader)

        print(f"Epoch {epoch+1}/{epochs}:")
        print(f"Train Loss: {avg_train_loss:.4f}, Train Accuracy: {train_accuracy:.4f}")
        print(f"Test Loss: {avg_test_loss:.4f}, Test Accuracy: {test_accuracy:.4f}")
        print('-'*30)

# Jalankan training
train_and_evaluate(model, criterion, optimizer, train_loader, test_loader, epochs=10)

# 7️⃣ **Simpan Model ke ONNX**
dummy_input = torch.randn(1, 3, 224, 224).to(device)
torch.onnx.export(model, dummy_input, "python/eyecare.onnx", opset_version=11)
print("✅ Model berhasil dikonversi ke ONNX!")