from pathlib import Path
import random
import shutil

# ==========================================
# 1. LOKASI DATASET
# ==========================================

ROOT = Path(__file__).resolve().parent.parent / "datasets" / "dataset_validasi_ikan"

SOURCE = ROOT / "data_lain"

TRAIN = ROOT / "train" / "data_lain"
VAL = ROOT / "val" / "data_lain"
TEST = ROOT / "test" / "data_lain"

# ==========================================
# 2. EKSTENSI GAMBAR
# ==========================================

EXTENSIONS = {
    ".jpg",
    ".jpeg",
    ".png",
    ".webp",
    ".bmp"
}

# ==========================================
# 3. PENGATURAN ACAK
# ==========================================

random.seed(42)

# ==========================================
# 4. MEMBACA GAMBAR SUMBER
# ==========================================

if not SOURCE.exists():
    raise FileNotFoundError(
        f"Folder sumber tidak ditemukan: {SOURCE}"
    )

images = [
    file for file in SOURCE.iterdir()
    if file.is_file()
    and file.suffix.lower() in EXTENSIONS
]

print(f"Jumlah foto ditemukan: {len(images)}")

train_images = images[:70]
val_images = images[70:85]
test_images = images[85:100]

# ==========================================
# 5. MEMERIKSA FOLDER TUJUAN
# ==========================================

for folder in [TRAIN, VAL, TEST]:

    folder.mkdir(parents=True, exist_ok=True)

    existing_images = [
        file for file in folder.iterdir()
        if file.is_file()
        and file.suffix.lower() in EXTENSIONS
    ]

    if existing_images:
        raise FileExistsError(
            f"Folder tujuan sudah berisi gambar: {folder}\n"
            "Periksa folder tersebut sebelum menjalankan ulang."
        )

# ==========================================
# 6. MENGACAK GAMBAR
# ==========================================

random.shuffle(images)

# ==========================================
# 7. MEMBAGI DATASET
# ==========================================

train_images = images[:72]
val_images = images[72:87]
test_images = images[87:102]

# ==========================================
# 8. MENYALIN GAMBAR
# ==========================================

def copy_images(image_list, destination):

    for image in image_list:
        shutil.copy2(
            image,
            destination / image.name
        )

copy_images(train_images, TRAIN)
copy_images(val_images, VAL)
copy_images(test_images, TEST)

# ==========================================
# 9. MENAMPILKAN HASIL
# ==========================================

print("\n================================")
print("PEMBAGIAN DATASET BERHASIL")
print("================================")

print(f"Train : {len(train_images)} foto")
print(f"Val   : {len(val_images)} foto")
print(f"Test  : {len(test_images)} foto")

print("--------------------------------")
print(f"Total : {len(train_images) + len(val_images) + len(test_images)} foto")
print("================================")

print("\nFolder sumber tetap utuh.")