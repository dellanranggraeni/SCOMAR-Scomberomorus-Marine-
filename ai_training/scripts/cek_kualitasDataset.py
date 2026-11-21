from pathlib import Path
import cv2
import csv

# Lokasi dataset
BASE_DIR = Path(__file__).resolve().parents[1]

DATASET_DIR = BASE_DIR / "datasets" / "mata" / "KesegaranMata"

# Folder hasil pemeriksaan
OUTPUT_DIR = BASE_DIR / "runs" / "cek_kualitas_dataset"
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

# Batas awal deteksi blur
BATAS_BLUR = 100

# Format gambar yang diperiksa
FORMAT_GAMBAR = [".jpg", ".jpeg", ".png", ".webp"]

hasil = []

# Memeriksa train, val, dan test
for bagian in ["train", "val", "test"]:

    for kelas in ["Fresh", "Non_Fresh"]:

        folder = DATASET_DIR / bagian / kelas

        if not folder.exists():
            print(f"Folder tidak ditemukan: {folder}")
            continue

        gambar_list = [
            p for p in folder.iterdir()
            if p.suffix.lower() in FORMAT_GAMBAR
        ]

        print(f"\nMemeriksa {bagian}/{kelas}: {len(gambar_list)} gambar")

        for image_path in gambar_list:

            gambar = cv2.imread(str(image_path))

            if gambar is None:
                nilai_blur = 0
                status = "Gagal - Tidak Dapat Dibaca"

            else:
                abu_abu = cv2.cvtColor(
                    gambar,
                    cv2.COLOR_BGR2GRAY
                )

                nilai_blur = cv2.Laplacian(
                    abu_abu,
                    cv2.CV_64F
                ).var()
                
                if nilai_blur < BATAS_BLUR:
                    status = "Gagal - Gambar Buram"
                else:
                    status = "Lolos Pemeriksaan"

            hasil.append({
                "bagian_dataset": bagian,
                "kelas": kelas,
                "nama_gambar": image_path.name,
                "nilai_ketajaman": round(float(nilai_blur), 2),
                "status": status
            })

            print(f"{image_path.name} | {status}")

# Simpan hasil pemeriksaan
csv_path = OUTPUT_DIR / "hasil_cek_kualitas.csv"

with open(csv_path, "w", newline="", encoding="utf-8-sig") as file:

    kolom = [
        "bagian_dataset",
        "kelas",
        "nama_gambar",
        "nilai_ketajaman",
        "status"
    ]

    writer = csv.DictWriter(file, fieldnames=kolom)
    writer.writeheader()
    writer.writerows(hasil)

print("\nPEMERIKSAAN SELESAI!")
print(f"Total gambar diperiksa: {len(hasil)}")
print(f"Hasil tersimpan di: {csv_path}")