from pathlib import Path
from PIL import Image, ImageDraw
import csv

ROOT = Path(__file__).resolve().parents[1]

DATASET_DIR = (
    ROOT / "datasets" / "jenis_ikan" / "PengujianEksternal_Genus" / "NON-SCOMBEROMORUS"
)

CSV_PATH = (
    ROOT / "runs" / "uji_eksternal_genus" / "hasil_prediksi_genus.csv"
)

OUTPUT = (
    ROOT / "runs" / "uji_eksternal_genus" / "pratinjau_8_prediksi_salah.jpg"
)

with open(CSV_PATH, encoding="utf-8-sig") as f:
    data = list(csv.DictReader(f))

salah = [
    row for row in data
    if row["label_asli"] == "NON-SCOMBEROMORUS"
    and row["prediksi"] == "Scomberomorus"
]

kolom = 4
lebar = 300
tinggi = 260
baris = (len(salah) + kolom - 1) // kolom

kanvas = Image.new(
    "RGB",
    (kolom * lebar, max(1, baris) * tinggi),
    "white"
)

draw = ImageDraw.Draw(kanvas)

for i, item in enumerate(salah):
    file = DATASET_DIR / item["nama_file"]

    with Image.open(file) as img:
        gambar = img.convert("RGB")
        gambar.thumbnail((280, 205))

    x = (i % kolom) * lebar
    y = (i // kolom) * tinggi

    kanvas.paste(gambar, (x + 10, y + 5))

    draw.text(
        (x + 10, y + 215),
        f"{item['nama_file']} | "
        f"Confidence: {item['confidence']}",
        fill="black"
    )

OUTPUT.parent.mkdir(parents=True, exist_ok=True)
kanvas.save(OUTPUT, quality=95)

print("Jumlah gambar salah:", len(salah))
print("Pratinjau tersimpan:", OUTPUT)