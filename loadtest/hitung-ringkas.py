#!/usr/bin/env python3
"""Menghitung statistik pengujian dari hasil.jtl hanya pada jendela stabil (setelah ramp-up).

Pemakaian: hitung-ringkas.py <hasil.jtl> <halaman> <rampup_detik> [keluaran.txt]
"""
import csv
import sys

# Label sampler yang dihitung per halaman (login awal halaman pembeli tidak ikut dihitung)
LABEL = {
    "beranda": {"beranda"}, "detail": {"detail"},
    "login": {"login-GET", "login-POST"}, "register": {"register-GET", "register-POST"},
    "cart": {"cart"}, "checkout": {"checkout"},
    "transaksi": {"transaksi"}, "riwayat": {"riwayat"},
}


def persentil(urut, p):
    """Persentil metode nearest-rank pada daftar yang sudah terurut."""
    if not urut:
        return 0
    idx = max(0, min(len(urut) - 1, -(-len(urut) * p // 100) - 1))
    return urut[int(idx)]


def main():
    if len(sys.argv) < 4:
        sys.exit(__doc__)
    jtl, halaman, rampup = sys.argv[1], sys.argv[2], int(sys.argv[3])
    keluaran = sys.argv[4] if len(sys.argv) > 4 else None
    label = LABEL[halaman]

    baris = []
    with open(jtl, newline="", encoding="utf-8") as f:
        for r in csv.DictReader(f):
            if r["label"] in label:
                baris.append((int(r["timeStamp"]), int(r["elapsed"]), r["success"] == "true"))
    if not baris:
        sys.exit("Tidak ada sampel untuk halaman ini pada hasil.jtl")

    awal = min(t for t, _, _ in baris)
    stabil = [b for b in baris if b[0] >= awal + rampup * 1000]
    if not stabil:  # run terlalu pendek / berhenti dini: jangan menyembunyikan, hitung semuanya
        stabil, catatan = baris, "PERINGATAN: tidak ada sampel setelah ramp-up, seluruh data dipakai"
    else:
        catatan = f"jendela stabil: setelah {rampup} detik pertama"

    n = len(stabil)
    gagal = sum(1 for _, _, ok in stabil if not ok)
    waktu = sorted(e for _, e, _ in stabil)
    durasi = max(1, (max(t for t, _, _ in stabil) - min(t for t, _, _ in stabil)) / 1000)
    hasil = [
        f"halaman={halaman}", catatan, f"sampel_total={len(baris)} sampel_stabil={n}",
        f"galat_persen={100 * gagal / n:.2f}",
        f"rata_ms={sum(waktu) / n:.0f} median_ms={persentil(waktu, 50)}",
        f"p90_ms={persentil(waktu, 90)} p95_ms={persentil(waktu, 95)} p99_ms={persentil(waktu, 99)}",
        f"throughput_per_detik={n / durasi:.2f}",
    ]
    teks = "\n".join(hasil)
    print(teks)
    if keluaran:
        with open(keluaran, "w", encoding="utf-8") as f:
            f.write(teks + "\n")


if __name__ == "__main__":
    main()
