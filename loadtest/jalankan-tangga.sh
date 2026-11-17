#!/bin/bash
# jalankan-tangga: menjalankan skenario satu halaman dari yang teringan ke terberat,
# berhenti menaikkan beban bila halaman sudah gagal atau rem darurat aktif, dan
# MENCATAT ALASAN untuk setiap skenario yang dilewati.
# Pemakaian : jalankan-tangga <halaman> [label]
# Keluaran  : /root/loadtest/hasil/tangga-<halaman>-<waktu>.tsv

HASIL_DASAR=/root/loadtest/hasil
BATAS_GAGAL=${BATAS_GAGAL:-50}   # % galat jendela stabil yang dianggap "sudah gagal" (angka awal, belum disepakati)
URUTAN=(baseline peak highpeak stress spike)

HALAMAN="${1:?Pemakaian: jalankan-tangga <halaman> [label]}"; LABEL="${2:-s3}"
CATATAN="$HASIL_DASAR/tangga-${HALAMAN}-$(date +%Y%m%d-%H%M%S).tsv"
mkdir -p "$HASIL_DASAR"
printf 'skenario\tstatus\talasan\tfolder_hasil\n' > "$CATATAN"

BERHENTI=""      # berisi alasan bila tangga harus dihentikan
for S in "${URUTAN[@]}"; do
  if [ -n "$BERHENTI" ]; then
    printf '%s\tDILEWATI\t%s\t-\n' "$S" "$BERHENTI" >> "$CATATAN"
    continue
  fi
  jalankan-uji "$HALAMAN" "$S" "$LABEL"
  KODE=$?
  DIR=$(cat "$HASIL_DASAR/terakhir.txt")

  if [ "$KODE" -eq 3 ]; then
    ALASAN="rem darurat aktif pada skenario $S: $(cat "$DIR/HENTI.txt")"
    printf '%s\tDIHENTIKAN\t%s\t%s\n' "$S" "$ALASAN" "$DIR" >> "$CATATAN"
    BERHENTI="skenario sebelumnya ($S) dihentikan rem darurat demi keamanan server: $(cat "$DIR/HENTI.txt")"
    continue
  elif [ "$KODE" -ne 0 ]; then
    printf '%s\tGALAT-SKRIP\tkode keluar %s\t%s\n' "$S" "$KODE" "$DIR" >> "$CATATAN"
    BERHENTI="skenario $S gagal dijalankan (kode $KODE), perlu diperiksa manual"
    continue
  fi

  GALAT=$(grep '^galat_persen=' "$DIR/ringkasan-stabil.txt" | cut -d= -f2)
  printf '%s\tSELESAI\tgalat_stabil=%s%%\t%s\n' "$S" "${GALAT:-?}" "$DIR" >> "$CATATAN"
  if [ -n "$GALAT" ] && awk -v g="$GALAT" -v b="$BATAS_GAGAL" 'BEGIN {exit !(g > b)}'; then
    BERHENTI="skenario $S sudah gagal (galat ${GALAT}% > ${BATAS_GAGAL}%), beban lebih tinggi tidak dijalankan agar server tidak dibebani tanpa tambahan informasi"
  fi
done
echo "Catatan tangga: $CATATAN"; cat "$CATATAN"
