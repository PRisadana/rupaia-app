#!/bin/bash
# jalankan-uji: menjalankan satu pengujian JMeter, merekam sumber daya server,
# dan mengawasinya dengan rem darurat (watchdog).
# Pemakaian : jalankan-uji <halaman> <skenario> [label]
# halaman   : beranda | detail | login | register | cart | checkout | transaksi | riwayat
# skenario  : uji | baseline | peak | highpeak | stress | spike
# label     : s3 (bawaan) atau lokal; hanya penanda nama folder hasil
# Kode keluar: 0 selesai normal, 3 dihentikan rem darurat, lainnya galat.

JMETER_HOME=/opt/apache-jmeter-5.6.3
APP=/www/wwwroot/rupaia
RENCANA=$APP/loadtest/rupaia.jmx
ENV_APP=$APP/.env
HASIL_DASAR=/root/loadtest/hasil
HITUNG=/usr/local/bin/hitung-ringkas
JEDA_PENDINGIN=${JEDA_PENDINGIN:-120}      # detik minimal sebelum pengecekan pemulihan
PEMULIHAN_MAKS=${PEMULIHAN_MAKS:-600}      # detik tambahan maksimal menunggu server pulih
BEBAN_PULIH=${BEBAN_PULIH:-0.7}            # load average 1 menit dianggap pulih di bawah nilai ini
# Ambang rem darurat (angka awal, WAJIB dikalibrasi saat uji coba)
BATAS_SWAP_KBS=${BATAS_SWAP_KBS:-1000}     # KB/detik lalu-lintas swap (masuk+keluar)
SWAP_BERTURUT=${SWAP_BERTURUT:-3}          # berapa pengecekan (10 detik) berturut-turut
BATAS_DISK=${BATAS_DISK:-90}               # persen pemakaian disk
TOLERANSI_DURASI=${TOLERANSI_DURASI:-120}  # detik di atas durasi rencana sebelum dipaksa berhenti

[ "$(id -u)" -eq 0 ] || { echo "Jalankan sebagai root."; exit 1; }

HALAMAN="${1:-}"; SKENARIO="${2:-}"; LABEL="${3:-s3}"

# Parameter skenario sesuai tabel skripsi (durasi sudah termasuk ramp-up)
case "$SKENARIO" in
  uji)      PENGGUNA=10;   RAMPUP=5;   DURASI=30  ;;
  baseline) PENGGUNA=100;  RAMPUP=30;  DURASI=180 ;;
  peak)     PENGGUNA=250;  RAMPUP=60;  DURASI=300 ;;
  highpeak) PENGGUNA=500;  RAMPUP=100; DURASI=300 ;;
  stress)   PENGGUNA=1000; RAMPUP=120; DURASI=300 ;;
  spike)    PENGGUNA=1000; RAMPUP=1;   DURASI=180 ;;
  *) echo "Skenario tidak dikenal: '$SKENARIO'"; exit 1 ;;
esac

# Halaman menentukan grup pengguna dan perlu tidaknya login
case "$HALAMAN" in
  login|register)                 BARU=$PENGGUNA; TETAP=0; PERLU_LOGIN=false ;;
  beranda|detail)                 BARU=0; TETAP=$PENGGUNA; PERLU_LOGIN=false ;;
  cart|checkout|transaksi|riwayat) BARU=0; TETAP=$PENGGUNA; PERLU_LOGIN=true ;;
  *) echo "Halaman tidak dikenal: '$HALAMAN'"; exit 1 ;;
esac

# Prasyarat
[ -x "$JMETER_HOME/bin/jmeter" ] || { echo "JMeter tidak ditemukan di $JMETER_HOME"; exit 1; }
[ -f "$RENCANA" ] || { echo "Rencana uji tidak ditemukan: $RENCANA"; exit 1; }
command -v pidstat >/dev/null || { echo "pidstat belum terpasang (apt install sysstat)"; exit 1; }
command -v python3 >/dev/null || { echo "python3 tidak ditemukan"; exit 1; }
if [ "$HALAMAN" != "beranda" ] && [ "$HALAMAN" != "register" ]; then
  [ -f "$APP/storage/app/loadtest/pembeli.csv" ] || { echo "CSV data uji belum ada. Jalankan LoadTestSeeder."; exit 1; }
fi

MULAI="$(date '+%F %T')"
NAMA="${HALAMAN}-${SKENARIO}-${LABEL}-$(date +%Y%m%d-%H%M%S)"
DIR="$HASIL_DASAR/$NAMA"
mkdir -p "$DIR"
echo "$DIR" > "$HASIL_DASAR/terakhir.txt"
export HEAP="-Xms512m -Xmx1g -XX:MaxMetaspaceSize=256m"

# Soket MySQL: aaPanel tidak memakai lokasi bawaan Linux, jadi dicari otomatis.
# Bisa ditentukan manual: MYSQL_SOCKET=/jalur/soket jalankan-uji ...
if [ -z "${MYSQL_SOCKET:-}" ]; then
  for S in /tmp/mysql.sock /run/mysqld/mysqld.sock /var/lib/mysql/mysql.sock; do
    if [ -S "$S" ]; then MYSQL_SOCKET=$S; break; fi
  done
fi
# ping_db berhasil bila server MySQL hidup. Sandi tidak diperlukan: jawaban "Access denied"
# justru membuktikan server menjawab. Server mati menghasilkan "Can't connect", yang dianggap gagal.
ping_db() {
  { [ -n "${MYSQL_SOCKET:-}" ] && command -v mysqladmin >/dev/null; } || return 1
  mysqladmin --socket="$MYSQL_SOCKET" ping 2>&1 | grep -qE 'is alive|Access denied'
}
if ping_db; then
  echo "Rem MySQL: AKTIF (soket $MYSQL_SOCKET)"
else
  echo "PERINGATAN: rem MySQL NONAKTIF (soket tidak ditemukan atau ping gagal). Atur MYSQL_SOCKET bila perlu."
fi

# ---------- Rem darurat: berjalan di latar belakang selama JMeter hidup ----------
awasi() {
  local pid_awal_oom jumlah_swap=0 gagal_db=0 mulai_detik pswp_lama pswp_baru laju
  pid_awal_oom=$(dmesg 2>/dev/null | grep -ci 'out of memory')
  mulai_detik=$(date +%s)
  pswp_lama=$(awk '/^pswpin|^pswpout/ {s+=$2} END {print s+0}' /proc/vmstat)
  local bisa_ping=0
  ping_db && bisa_ping=1
  while sleep 10; do
    pgrep -f ApacheJMeter.jar >/dev/null || return 0     # JMeter sudah selesai
    # 1) OOM baru di log kernel
    if [ "$(dmesg 2>/dev/null | grep -ci 'out of memory')" -gt "$pid_awal_oom" ]; then
      alasan="OOM kernel terdeteksi (proses dimatikan karena kehabisan memori)"; break; fi
    # 2) Lalu-lintas swap tinggi berturut-turut (halaman memori sedang dipertukarkan)
    pswp_baru=$(awk '/^pswpin|^pswpout/ {s+=$2} END {print s+0}' /proc/vmstat)
    laju=$(( (pswp_baru - pswp_lama) * 4 / 10 ))          # halaman 4 KB per 10 detik -> KB/detik
    pswp_lama=$pswp_baru
    if [ "$laju" -gt "$BATAS_SWAP_KBS" ]; then jumlah_swap=$((jumlah_swap+1)); else jumlah_swap=0; fi
    if [ "$jumlah_swap" -ge "$SWAP_BERTURUT" ]; then
      alasan="lalu-lintas swap > ${BATAS_SWAP_KBS} KB/detik selama $((SWAP_BERTURUT*10)) detik"; break; fi
    # 3) MySQL tidak menjawab dua kali berturut-turut
    if [ "$bisa_ping" -eq 1 ]; then
      if ping_db; then gagal_db=0; else gagal_db=$((gagal_db+1)); fi
      if [ "$gagal_db" -ge 2 ]; then alasan="MySQL tidak menjawab ping dua kali berturut-turut"; break; fi
    fi
    # 4) Disk hampir penuh
    if [ "$(df --output=pcent / | tail -n 1 | tr -dc '0-9')" -gt "$BATAS_DISK" ]; then
      alasan="pemakaian disk > ${BATAS_DISK}%"; break; fi
    # 5) Pengujian melewati durasi rencana
    if [ $(( $(date +%s) - mulai_detik )) -gt $(( DURASI + TOLERANSI_DURASI )) ]; then
      alasan="pengujian melewati durasi rencana + ${TOLERANSI_DURASI} detik"; break; fi
  done
  echo "$alasan" > "$DIR/HENTI.txt"
  pkill -f ApacheJMeter.jar
}

# ---------- Pemanasan ----------
if [ "$SKENARIO" != "uji" ]; then
  echo "Pemanasan 20 detik..."
  WB=0; WT=0
  [ "$BARU" -gt 0 ] && WB=5
  [ "$TETAP" -gt 0 ] && WT=5
  "$JMETER_HOME/bin/jmeter" -n -t "$RENCANA" -l /dev/null -j /dev/null \
    -Jhalaman="$HALAMAN" -Jperlu_login="$PERLU_LOGIN" \
    -Jpengguna_baru="$WB" -Jpengguna_tetap="$WT" -Jrampup=5 -Jdurasi=20 >/dev/null 2>&1
fi

# ---------- Pengujian ----------
free -m > "$DIR/memori-sebelum.txt"
uptime > "$DIR/beban-sebelum.txt"
vmstat -t 5 > "$DIR/vmstat.txt" &
PID_VMSTAT=$!

echo "Menjalankan: $HALAMAN, $SKENARIO ($PENGGUNA pengguna, ramp-up ${RAMPUP}s, durasi ${DURASI}s), label $LABEL"
"$JMETER_HOME/bin/jmeter" -n -t "$RENCANA" -l "$DIR/hasil.jtl" -j "$DIR/jmeter.log" \
  -Jhalaman="$HALAMAN" -Jperlu_login="$PERLU_LOGIN" \
  -Jpengguna_baru="$BARU" -Jpengguna_tetap="$TETAP" \
  -Jrampup="$RAMPUP" -Jdurasi="$DURASI" > "$DIR/konsol.txt" 2>&1 &
PID_JMETER=$!

sleep 5
PID_JAVA="$(pgrep -f ApacheJMeter.jar | head -n 1)"
if [ -n "$PID_JAVA" ]; then
  pidstat -u -r -p "$PID_JAVA" 5 > "$DIR/pidstat-jmeter.txt" &
  PID_PIDSTAT=$!
fi
awasi &
PID_AWAS=$!

wait "$PID_JMETER"; KODE=$?
kill "$PID_AWAS" "$PID_VMSTAT" 2>/dev/null
[ -n "${PID_PIDSTAT:-}" ] && kill "$PID_PIDSTAT" 2>/dev/null

free -m > "$DIR/memori-sesudah.txt"
uptime > "$DIR/beban-sesudah.txt"
cek-rupaia > "$DIR/cek-rupaia.txt" 2>&1

# ---------- Pasca-pengujian (setelah perekaman sumber daya berhenti) ----------
# Statistik pada jendela stabil, dan laporan HTML dibuat di sini agar tidak mengotori vmstat/pidstat
python3 -I "$HITUNG" "$DIR/hasil.jtl" "$HALAMAN" "$RAMPUP" "$DIR/ringkasan-stabil.txt" > /dev/null 2>&1 \
  || echo "statistik gagal dihitung (hasil.jtl kosong atau JMeter dihentikan dini)" > "$DIR/ringkasan-stabil.txt"
"$JMETER_HOME/bin/jmeter" -g "$DIR/hasil.jtl" -o "$DIR/laporan" >> "$DIR/konsol.txt" 2>&1

STATUS="normal"; KODE_AKHIR=0
if [ -f "$DIR/HENTI.txt" ]; then STATUS="DIHENTIKAN: $(cat "$DIR/HENTI.txt")"; KODE_AKHIR=3; fi
{
  echo "halaman=$HALAMAN"; echo "skenario=$SKENARIO"; echo "label=$LABEL"
  echo "pengguna=$PENGGUNA rampup=${RAMPUP}s durasi=${DURASI}s"
  echo "mulai=$MULAI selesai=$(date '+%F %T') kode_jmeter=$KODE"
  echo "status=$STATUS"
  echo "private_disk=$(grep -E '^RUPAIA_PRIVATE_DISK=' "$ENV_APP" | cut -d= -f2)"
} > "$DIR/ringkasan.txt"

echo "Selesai ($STATUS). Hasil: $DIR"
cat "$DIR/ringkasan-stabil.txt"

# ---------- Pendinginan berbasis pemulihan ----------
if [ "$SKENARIO" != "uji" ]; then
  echo "Pendinginan minimal ${JEDA_PENDINGIN} detik, lalu menunggu load average < ${BEBAN_PULIH}..."
  sleep "$JEDA_PENDINGIN"
  T=0
  while [ "$T" -lt "$PEMULIHAN_MAKS" ] && awk -v b="$BEBAN_PULIH" '{exit !($1 >= b)}' /proc/loadavg; do sleep 10; T=$((T+10)); done
  echo "pemulihan_tambahan_detik=$T load=$(cut -d' ' -f1 /proc/loadavg)" | tee -a "$DIR/ringkasan.txt"
fi
exit "$KODE_AKHIR"
