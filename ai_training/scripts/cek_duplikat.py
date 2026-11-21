
from pathlib import Path
from collections import defaultdict
from PIL import Image
import hashlib

ROOT = Path(__file__).resolve().parents[1]
DATASET_DIR = ROOT / "datasets" / "mata" / "KesegaranMata_Revisi"

EXTENSIONS = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}

gambar_hash = defaultdict(list)

for split in ["train", "val", "test"]:
    for kelas in ["Fresh", "Non_Fresh"]:
        folder = DATASET_DIR / split / kelas

        for file in folder.iterdir():
            if file.suffix.lower() not in EXTENSIONS:
                continue

            with Image.open(file) as img:
                img.load()
                isi = img.convert("RGB").tobytes()

            hash_gambar = hashlib.sha256(isi).hexdigest()
            gambar_hash[hash_gambar].append(file)

duplikat = {
    nilai: file_list
    for nilai, file_list in gambar_hash.items()
    if len(file_list) > 1
}

print("\n===== HASIL PEMERIKSAAN DUPLIKAT =====")
print("Total gambar unik:", len(gambar_hash))
print("Kelompok gambar duplikat:", len(duplikat))

for file_list in duplikat.values():
    print("\nDuplikat ditemukan:")
    for file in file_list:
        print("-", file.relative_to(DATASET_DIR))