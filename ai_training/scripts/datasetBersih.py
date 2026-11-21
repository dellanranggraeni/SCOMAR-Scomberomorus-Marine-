from pathlib import Path
import pandas as pd
import shutil

# Lokasi utama
BASE_DIR = Path(__file__).resolve().parents[1]

DATASET_DIR = BASE_DIR / "datasets" / "mata" / "KesegaranMata"

CSV_PATH = BASE_DIR / "runs" / "cek_kualitas_dataset" / "hasil_cek_kualitas.csv"

# Folder dataset bersih
OUTPUT_DIR = BASE_DIR / "datasets" / "mata" / "KesegaranMata_Bersih"

# Membaca hasil pemeriksaan kualitas
df = pd.read_csv(CSV_PATH)

# Hanya mengambil gambar yang lolos
df_lolos = df[df["status"] == "Lolos Pemeriksaan"]

jumlah = 0

for _, row in df_lolos.iterrows():

    bagian = row["bagian_dataset"]
    kelas = row["kelas"]
    nama_gambar = row["nama_gambar"]

    sumber = DATASET_DIR / bagian / kelas / nama_gambar
    tujuan_folder = OUTPUT_DIR / bagian / kelas

    tujuan_folder.mkdir(parents=True, exist_ok=True)

    if sumber.exists():
        shutil.copy2(sumber, tujuan_folder / nama_gambar)
        jumlah += 1

print("\nPEMBUATAN DATASET BERSIH SELESAI!")
print(f"Total gambar disalin: {jumlah}")
print(f"Lokasi dataset: {OUTPUT_DIR}")