from pathlib import Path
from ultralytics import YOLO
import csv

ROOT = Path(__file__).resolve().parents[1]

# Model insang terbaik
MODEL_PATH = (
    ROOT / "runs" / "kesegaran_insang_v1" / "weights" / "best.pt"
)

# Dataset pengujian eksternal
DATASET_DIR = (
    ROOT / "datasets" / "insang" / "PengujianEksternal_Kesegaran" / "INSANG"
)

OUTPUT_DIR = ROOT / "runs" / "uji_eksternal_insang"
OUTPUT_DIR.mkdir(parents=True, exist_ok=True)

CSV_OUTPUT = OUTPUT_DIR / "hasil_prediksi_insang.csv"

EXTENSIONS = {".jpg", ".jpeg", ".png", ".webp", ".bmp"}

LABEL_MAP = {
    "FRESH": "FRESH",
    "NON-FRESH": "NON-FRESH",
}


def normalisasi(label):
    return "".join(
        karakter for karakter in str(label).lower()
        if karakter.isalnum()
    )


def main():
    if not MODEL_PATH.exists():
        raise FileNotFoundError(
            f"Model tidak ditemukan: {MODEL_PATH}"
        )

    if not DATASET_DIR.exists():
        raise FileNotFoundError(
            f"Folder dataset tidak ditemukan: {DATASET_DIR}"
        )

    model = YOLO(str(MODEL_PATH))

    print("MODEL:", MODEL_PATH)
    print("KELAS MODEL:", model.names)

    hasil = []

    for folder in sorted(DATASET_DIR.iterdir()):

        if not folder.is_dir():
            continue

        kelas_asli = LABEL_MAP.get(folder.name.upper())

        if kelas_asli is None:
            continue

        files = sorted([
            file for file in folder.rglob("*")
            if file.is_file()
            and file.suffix.lower() in EXTENSIONS
        ])

        print(f"\n===== {kelas_asli.upper()} =====")
        print("Jumlah foto:", len(files))

        for file in files:

            try:
                prediksi = model.predict(
                    source=str(file),
                    imgsz=224,
                    verbose=False
                )[0]

                if prediksi.probs is None:
                    raise ValueError(
                        "Hasil klasifikasi tidak tersedia"
                    )

                indeks = int(prediksi.probs.top1)
                prediksi_kelas = model.names[indeks]

                confidence = float(
                    prediksi.probs.top1conf.item()
                )

                benar = (
                    normalisasi(prediksi_kelas)
                    == normalisasi(kelas_asli)
                )

                status = "BENAR" if benar else "SALAH"

                hasil.append({
                    "nama_file": file.name,
                    "label_asli": kelas_asli,
                    "prediksi": prediksi_kelas,
                    "confidence": round(confidence, 4),
                    "hasil": status
                })

                print(
                    f"{file.name} | "
                    f"Prediksi: {prediksi_kelas} | "
                    f"Confidence: {confidence:.4f} | "
                    f"{status}"
                )

            except Exception as error:
                print(f"GAGAL: {file.name} | {error}")

    # Simpan hasil prediksi
    with open(
        CSV_OUTPUT,
        "w",
        newline="",
        encoding="utf-8-sig"
    ) as file:

        kolom = [
            "nama_file",
            "label_asli",
            "prediksi",
            "confidence",
            "hasil"
        ]

        writer = csv.DictWriter(file, fieldnames=kolom)
        writer.writeheader()
        writer.writerows(hasil)

    # Rekap hasil
    print("\n===== REKAP PENGUJIAN EKSTERNAL INSANG =====")

    total_benar = 0

    for kelas in ["Fresh", "Non_Fresh"]:

        data = [
            item for item in hasil
            if normalisasi(item["label_asli"])
            == normalisasi(kelas)
        ]

        benar = sum(
            item["hasil"] == "BENAR"
            for item in data
        )

        salah = len(data) - benar
        total_benar += benar

        akurasi = (
            benar / len(data) * 100
            if data else 0
        )

        print(f"\n{kelas}")
        print("Total:", len(data))
        print("Benar:", benar)
        print("Salah:", salah)
        print(f"Akurasi: {akurasi:.2f}%")

    total = len(hasil)

    print("\n===== TOTAL KESELURUHAN =====")
    print("Total foto berhasil diuji:", total)
    print("Prediksi benar:", total_benar)
    print("Prediksi salah:", total - total_benar)

    if total:
        print(
            f"Akurasi eksternal: "
            f"{total_benar / total * 100:.2f}%"
        )

    print("\nCSV tersimpan di:")
    print(CSV_OUTPUT)


if __name__ == "__main__":
    main()