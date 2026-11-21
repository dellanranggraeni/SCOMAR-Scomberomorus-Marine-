from pathlib import Path
from PIL import Image, ImageDraw
import csv

ROOT = Path(__file__).resolve().parents[1]

DATASET_DIR = ROOT / "datasets" / "insang" / "KesegaranInsang"
AUDIT_DIR = ROOT / "runs" / "audit_kualitas_insang"

CSV_PATH = AUDIT_DIR / "daftar_train_ketajaman_di_bawah_90.csv"

def pilih_sampel(data, jumlah=12):
    data = sorted(
        data,
        key=lambda x: float(x["nilai_ketajaman"])
    )

    if len(data) <= jumlah:
        return data

    indeks = [
        round(i * (len(data) - 1) / (jumlah - 1))
        for i in range(jumlah)
    ]

    return [data[i] for i in indeks]


with open(CSV_PATH, "r", encoding="utf-8-sig") as f:
    data = list(csv.DictReader(f))

for kelas in ["fresh", "non-fresh"]:

    sampel = [
        item for item in data
        if item["kelas"] == kelas
    ]

    sampel = pilih_sampel(sampel)

    kolom = 3
    lebar = 260
    tinggi = 225
    baris = (len(sampel) + kolom - 1) // kolom

    kanvas = Image.new(
        "RGB",
        (kolom * lebar, baris * tinggi),
        "white"
    )

    draw = ImageDraw.Draw(kanvas)

    for i, item in enumerate(sampel):
        file = (
            DATASET_DIR / "train" /
            kelas / item["nama_file"]
        )

        with Image.open(file) as img:
            gambar = img.convert("RGB")
            gambar.thumbnail((240, 170))

        x = (i % kolom) * lebar
        y = (i // kolom) * tinggi

        posisi_x = x + (240 - gambar.width) // 2
        kanvas.paste(gambar, (posisi_x, y + 5))

        teks = (
            f"{item['nama_file']}\n"
            f"Ketajaman: {item['nilai_ketajaman']}"
        )

        draw.text(
            (x + 5, y + 180),
            teks,
            fill="black"
        )

    output = AUDIT_DIR / f"pratinjau_{kelas}_di_bawah_90.jpg"
    kanvas.save(output, quality=95)

    print("Pratinjau tersimpan:", output)

print("\nPembuatan pratinjau selesai.")