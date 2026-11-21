from pathlib import Path
import shutil
import zipfile

ROOT = Path(__file__).resolve().parents[1]

BASE = (
    ROOT / "datasets" / "insang"/ "PengujianEksternal_Kesegaran" / "INSANG"
)

OUTPUT = (
    ROOT / "runs" / "uji_eksternal_insang" / "sampel_salah"
)

FOTO = {
    "FRESH": [
        "fresh-1.jpeg",
        "fresh-2.jpeg",
        "fresh-5.jpg",
        "fresh-7.jpg",
    ],
    "NON-FRESH": [
        "non-1.jpeg",
        "non-10.jpg",
        "non-2.jpeg",
        "non-9.jpg",
    ],
}

berhasil = 0
tidak_ditemukan = []

for kelas, daftar in FOTO.items():

    folder_sumber = BASE / kelas
    folder_tujuan = OUTPUT / kelas

    folder_tujuan.mkdir(parents=True, exist_ok=True)

    for nama in daftar:

        sumber = folder_sumber / nama

        if not sumber.exists():
            tidak_ditemukan.append(str(sumber))
            continue

        shutil.copy2(
            sumber,
            folder_tujuan / nama
        )

        print("Berhasil disalin:", kelas, nama)
        berhasil += 1

# Membuat ZIP hanya jika seluruh foto ditemukan
if tidak_ditemukan:
    print("\nADA FILE YANG TIDAK DITEMUKAN:")

    for file in tidak_ditemukan:
        print(file)

else:
    zip_path = (
        ROOT / "runs" / "uji_eksternal_insang" / "8_foto_salah.zip"
    )

    with zipfile.ZipFile(
        zip_path, "w", zipfile.ZIP_DEFLATED
    ) as zipf:

        for file in OUTPUT.rglob("*"):
            if file.is_file():
                zipf.write(
                    file,
                    file.relative_to(OUTPUT)
                )

    print("\n===== SELESAI =====")
    print("Total foto disalin:", berhasil)
    print("ZIP tersimpan:", zip_path)