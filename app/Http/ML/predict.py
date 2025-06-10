import os
import onnxruntime as ort
from PIL import Image
import torchvision.transforms as transforms
import sys
import json
import numpy as np

# Ambil path gambar dari argumen CLI
image_path = sys.argv[1]

# Preprocessing (harus sama dengan saat training)
transform = transforms.Compose([
    transforms.Resize((224, 224)),
    transforms.ToTensor(),
    transforms.Normalize([0.485, 0.456, 0.406],
                         [0.229, 0.224, 0.225])
])

image = Image.open(image_path).convert('RGB')
input_tensor = transform(image).unsqueeze(0).numpy()

# ✅ Load ONNX model
model_path = os.path.join(os.path.dirname(__file__), "model_eye_disease.onnx")
session = ort.InferenceSession(model_path)
input_name = session.get_inputs()[0].name
output = session.run(None, {input_name: input_tensor})

# Softmax
logits = output[0][0]
exp_scores = np.exp(logits - np.max(logits))
probabilities = exp_scores / np.sum(exp_scores)

# Klasifikasi
predicted_index = int(np.argmax(probabilities))
confidence = float(probabilities[predicted_index])

# Jika confidence < 60%, anggap sebagai "Normal"
if confidence < 0.6:
    class_index = -1  # Special case untuk 'Normal'
else:
    class_index = predicted_index

# Output JSON
print(json.dumps({
    "class_index": class_index,
    "accuracy": round(confidence, 4),
    "probabilities": [round(float(p), 4) for p in probabilities]
}))