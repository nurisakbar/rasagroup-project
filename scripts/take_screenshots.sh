#!/bin/bash
CHROME="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
OUT_DIR="/Applications/MAMP/htdocs/rasagroup/rasagroup-project/docs/manual_book_images"
ART_DIR="/Users/nurisakbar/.gemini/antigravity-ide/brain/3d77e286-5219-49d3-9d80-1cb781181f51/images"

mkdir -p "$OUT_DIR" "$ART_DIR"

echo "Capturing 01_daftar_pesanan.png..."
"$CHROME" --headless=new --disable-gpu --virtual-time-budget=5000 --window-size=1440,900 \
  --screenshot="$OUT_DIR/01_daftar_pesanan.png" \
  "http://localhost:8002/local-screenshot-auth?to=/admin/orders"

echo "Capturing 02_detail_pesanan_distributor_top.png..."
"$CHROME" --headless=new --disable-gpu --virtual-time-budget=5000 --window-size=1440,1100 \
  --screenshot="$OUT_DIR/02_detail_pesanan_distributor_top.png" \
  "http://localhost:8002/local-screenshot-auth?to=/admin/orders/01a0b559-0864-73df-a683-73a098c1df09"

echo "Capturing 03_verifikasi_transfer_manual.png..."
"$CHROME" --headless=new --disable-gpu --virtual-time-budget=5000 --window-size=1440,1100 \
  --screenshot="$OUT_DIR/03_verifikasi_transfer_manual.png" \
  "http://localhost:8002/local-screenshot-auth?to=/admin/orders/019fa2a6-61f5-7126-8ddf-30616c382acd"

echo "Capturing 04_pengiriman_ekspedisi_resi.png..."
"$CHROME" --headless=new --disable-gpu --virtual-time-budget=5000 --window-size=1440,1100 \
  --screenshot="$OUT_DIR/04_pengiriman_ekspedisi_resi.png" \
  "http://localhost:8002/local-screenshot-auth?to=/admin/orders/019fff27-19fb-710c-8048-601acd2a6124"

echo "Capturing 05_cetak_surat_jalan.png..."
"$CHROME" --headless=new --disable-gpu --virtual-time-budget=5000 --window-size=1200,900 \
  --screenshot="$OUT_DIR/05_cetak_surat_jalan.png" \
  "http://localhost:8002/local-screenshot-auth?to=/admin/orders/019fff27-19fb-710c-8048-601acd2a6124/surat-jalan"

# Copy to artifact directory as well
cp -r "$OUT_DIR"/* "$ART_DIR"/

echo "All screenshots captured successfully!"
ls -lh "$OUT_DIR"
