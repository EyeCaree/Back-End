import os
import shutil
import random

# Path dataset asli
dataset_path = "resources/assets/dataset"
train_path = "resources/assets/train"
test_path = "resources/assets/test"

# Persentase split
train_ratio = 0.8  # 80% untuk training, 20% untuk testing

# Buat folder train dan test jika belum ada
os.makedirs(train_path, exist_ok=True)
os.makedirs(test_path, exist_ok=True)

# Ambil semua kategori (nama folder dalam dataset/)
categories = os.listdir(dataset_path)

for category in categories:
    category_path = os.path.join(dataset_path, category)

    # Skip jika bukan folder
    if not os.path.isdir(category_path):
        continue

    # Buat folder untuk train dan test berdasarkan kategori
    os.makedirs(os.path.join(train_path, category), exist_ok=True)
    os.makedirs(os.path.join(test_path, category), exist_ok=True)

    # Ambil semua gambar dalam kategori
    images = os.listdir(category_path)
    random.shuffle(images)  # Acak urutan gambar

    # Tentukan indeks split
    split_idx = int(len(images) * train_ratio)

    # Pindahkan gambar ke folder train dan test
    for i, image in enumerate(images):
        src = os.path.join(category_path, image)
        if i < split_idx:
            dst = os.path.join(train_path, category, image)
        else:
            dst = os.path.join(test_path, category, image)
        
        shutil.move(src, dst)

print("Dataset berhasil dibagi ke dalam folder train/ dan test/")