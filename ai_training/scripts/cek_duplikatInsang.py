from pathlib import Path
from collections import defaultdict
from PIL import Image
import hashlib

ROOT = Path(__file__).resolve().parents[1]

DATASET_DIR = ROOT / "datasets" / "insang" / "KesegaranInsang"

CLASSES = ["fresh", "non-fresh"]
SPLITS = ["train", "val", "test"]

gambar_hash = defaultdict(list)

for split in SPLITS:
    for kelas in CLASSES:
        folder = DATASET_DIR / split / kelas

        for file in folder.iterdir():
            if not file.is_file():
                continue

            if file.suffix.lower() not in {
                ".jpg", ".jpeg", ".png", ".bmp", ".webp"
            }:
                continue

            with Image.open(file) as img:
                img = img.convert("RGB")
                ukuran = img.size
                isi = img.tobytes()

            data = str(ukuran).encode() + isi
            hash_gambar = hashlib.sha256(data).hexdigest()

            gambar_hash[hash_gambar].append(
                (file, split, kelas)
            )

duplikat = {
    nilai: daftar
    for nilai, daftar in gambar_hash.items()
    if len(daftar) > 1
}

print("\n===== HASIL PEMERIKSAAN DUPLIKAT INSANG =====")
print("Total gambar unik:", len(gambar_hash))
print("Kelompok gambar duplikat:", len(duplikat))

for daftar in duplikat.values():
    print("\nDuplikat ditemukan:")

    for file, split, kelas in daftar:
        print(f"- {file.name} | {split}/{kelas}")
