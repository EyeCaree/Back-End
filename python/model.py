import os
import torch
import torch.nn as nn
import torch.optim as optim
import torchvision.transforms as transforms
from torchvision import datasets
import onnx

# 1️⃣ Definisi Model CNN
class CNNModel(nn.Module):
    def __init__(self, num_classes=3):  # Sesuaikan jumlah kelas
        super(CNNModel, self).__init__()
        self.conv1 = nn.Conv2d(3, 32, kernel_size=3, stride=1, padding=1)
        self.relu = nn.ReLU()
        self.pool = nn.MaxPool2d(kernel_size=2, stride=2, padding=0)
        self.flatten_dim = 32 * 112 * 112  # Hitung otomatis nanti
        self.fc1 = nn.Linear(self.flatten_dim, num_classes)

    def forward(self, x):
        x = self.pool(self.relu(self.conv1(x)))
        x = torch.flatten(x, 1)  # Flatten layer
        x = self.fc1(x)
        return x

# 2️⃣ Preprocessing Dataset
transform = transforms.Compose([
    transforms.Resize((224, 224)),  # Ubah ukuran gambar ke 224x224
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.5, 0.5, 0.5], std=[0.5, 0.5, 0.5])  # Normalisasi gambar
])

# Path dataset
dataset_path = os.path.join(os.getcwd(), 'resources/assets/train')

# Cek apakah dataset ada
if not os.path.exists(dataset_path):
    raise FileNotFoundError(f"Folder dataset tidak ditemukan: {dataset_path}")

train_dataset = datasets.ImageFolder(root=dataset_path, transform=transform)
train_loader = torch.utils.data.DataLoader(dataset=train_dataset, batch_size=32, shuffle=True)

# 3️⃣ Inisialisasi Model
num_classes = len(train_dataset.classes)
model = CNNModel(num_classes=num_classes)
criterion = nn.CrossEntropyLoss()
optimizer = optim.Adam(model.parameters(), lr=0.001)

# 4️⃣ Training Model
epochs = 10  # Sesuaikan jumlah epoch
for epoch in range(epochs):
    total_loss = 0.0
    for images, labels in train_loader:
        outputs = model(images)
        loss = criterion(outputs, labels)
        optimizer.zero_grad()
        loss.backward()
        optimizer.step()
        total_loss += loss.item()
    print(f'Epoch [{epoch+1}/{epochs}], Loss: {total_loss / len(train_loader):.4f}')

# 5️⃣ Simpan Model ke Format ONNX
onnx_path = os.path.join(os.getcwd(), "python", "eyecare.onnx")
dummy_input = torch.randn(1, 3, 224, 224)  # Input contoh
torch.onnx.export(model, dummy_input, onnx_path, opset_version=11)

print(f"Model berhasil dikonversi ke ONNX dan disimpan di {onnx_path}!")