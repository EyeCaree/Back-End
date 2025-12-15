import sys
import os
import json
import tensorflow as tf
import numpy as np
from PIL import Image

# Matikan log TensorFlow
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'

# Path model
BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "../../.."))
MODEL_PATH = os.path.join(BASE_DIR, "app/Http/ML/eye_model_resnet50.keras")

# Load model
try:
    model = tf.keras.models.load_model(MODEL_PATH)
    class_names = ['Bulging_Eyes', 'Cataracts', 'Crossed_Eyes', 'Uveitis', 'Normal']
    img_size = (224, 224)
except Exception as e:
    print(json.dumps({"error": f"Gagal memuat model: {str(e)}"}))
    sys.exit(1)

# Validasi input
if len(sys.argv) < 2:
    print(json.dumps({"error": "Tidak ada path gambar diberikan."}))
    sys.exit(1)

image_path = sys.argv[1]

def preprocess_image(image_path):
    try:
        img = Image.open(image_path).convert("RGB")
        img = img.resize(img_size)
        img_array = np.array(img) / 255.0
        img_array = np.expand_dims(img_array, axis=0)
        return img_array
    except Exception as e:
        print(json.dumps({"error": f"Gagal memproses gambar: {str(e)}"}))
        sys.exit(1)

try:
    img_preprocessed = preprocess_image(image_path)
    predictions = model.predict(img_preprocessed)
    class_index = int(np.argmax(predictions))
    confidence = float(predictions[0][class_index])

    threshold = 0.30
    if confidence < threshold:
        output = {
            "prediction": "bukan mata",
            "confidence": confidence,
            "class_index": -1,
            "probabilities": predictions[0].tolist(),
            "accuracy": confidence
        }
    else:
        output = {
            "prediction": class_names[class_index],
            "confidence": confidence,
            "class_index": class_index,
            "probabilities": predictions[0].tolist(),
            "accuracy": confidence
        }

    print(json.dumps(output))
except Exception as e:
    print(json.dumps({"error": f"Gagal memproses gambar: {str(e)}"}))
    sys.exit(1)