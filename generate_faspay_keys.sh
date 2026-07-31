#!/usr/bin/env bash

# ==============================================================================
# Script: generate_faspay_keys.sh
# Deskripsi: Generate RSA 2048-bit Keys & jadikan .zip otomatis
# ==============================================================================

set -e

TARGET_DIR="storage/app"
PRIVATE_KEY="$TARGET_DIR/faspay_private_key.pem"
PUBLIC_KEY="$TARGET_DIR/faspay_public_key.pem"
ZIP_PUBLIC="$TARGET_DIR/faspay_public_key.zip"
ZIP_ALL="$TARGET_DIR/faspay_all_keys.zip"

echo "=============================================================================="
echo "  GENERATE FASPAY RSA 2048-BIT KEYS & AUTO-ZIP"
echo "=============================================================================="

if [ ! -d "$TARGET_DIR" ]; then
    mkdir -p "$TARGET_DIR"
fi

# 1. Generate Private Key RSA 2048-bit
echo ">> [1/5] Membuat Private Key RSA 2048-bit..."
openssl genrsa -out "$PRIVATE_KEY" 2048 2>/dev/null
echo "   [OK] Private Key: $PRIVATE_KEY"

# 2. Extract Public Key dari Private Key
echo ">> [2/5] Mengekstrak Public Key RSA..."
openssl rsa -in "$PRIVATE_KEY" -pubout -out "$PUBLIC_KEY" 2>/dev/null
echo "   [OK] Public Key: $PUBLIC_KEY"

# 3. Set hak akses file (Permissions)
echo ">> [3/5] Mengatur keamanan hak akses file..."
chmod 600 "$PRIVATE_KEY"
chmod 644 "$PUBLIC_KEY"
echo "   [OK] chmod 600 & 644 berhasil diatur."

# 4. Membuat file .ZIP agar mudah didownload
echo ">> [4/5] Membuat file .zip otomatis..."
(
    cd "$TARGET_DIR"
    zip -q -o faspay_public_key.zip faspay_public_key.pem
    zip -q -o faspay_all_keys.zip faspay_private_key.pem faspay_public_key.pem
)
echo "   [OK] ZIP Public Key: $ZIP_PUBLIC"
echo "   [OK] ZIP Semua Kunci: $ZIP_ALL"

# 5. Tampilkan Isi Public Key
echo ""
echo "=============================================================================="
echo "  BERIKUT ISI PUBLIC KEY ANDA (Salin ke Faspay):"
echo "=============================================================================="
cat "$PUBLIC_KEY"
echo "=============================================================================="
echo ""
echo ">> SELESAI! File ZIP yang siap Anda download di server:"
echo "   1. Khusus Public Key (Untuk Faspay): $ZIP_PUBLIC"
echo "   2. Semua Kunci (Backup Private & Public): $ZIP_ALL"
echo ""
