from pathlib import Path
from collections import defaultdict
from PIL import Image
import hashlib

ROOT = Path(__file__).resolve().parents[1]

DATASET_DIR = (
    ROOT / "datasets" / "jenis_ikan" / "IdentifikasiGenus"
)

SPLITS = ["train", "val", "test"]
CLASSES = ["Scomberomorus", "Non_Scomberomorus"]

EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp", ".bmp"}

gambar_hash = defaultdict(list)

for split in SPLITS:
    for kelas in CLASSES:
        folder = DATASET_DIR / split / kelas

        for file in folder.iterdir():
            if not file.is_file():
                continue

            if file.suffix.lower() not in EXTENSIONS:
                continue

            with Image.open(file) as img:
                gambar = img.convert("RGB")
                data = str(gambar.size).encode() + gambar.tobytes()

            hash_gambar = hashlib.sha256(data).hexdigest()
            gambar_hash[hash_gambar].append(
                (file, split, kelas)
            )

duplikat = {
    nilai: daftar
    for nilai, daftar in gambar_hash.items()
    if len(daftar) > 1
}

print("\n===== HASIL PEMERIKSAAN DUPLIKAT GENUS =====")
print("Total gambar unik:", len(gambar_hash))
print("Kelompok gambar duplikat:", len(duplikat))

for daftar in duplikat.values():
    print("\nDuplikat ditemukan:")
    for file, split, kelas in daftar:
        print(f"- {file.name} | {split}/{kelas}")