#!/bin/bash
FILES=(
    "app/Jobs/CreateShipmentBooking.php"
    "app/Services/EkspedisiKuService.php"
)

# Tar the files
tar -czf update_lalamove.tar.gz "${FILES[@]}"

# Upload to tmp
sshpass -p 'STTIndo26' scp -o StrictHostKeyChecking=no -P 2122 update_lalamove.tar.gz debian@116.206.199.251:/tmp/

# Extract on server using sudo
sshpass -p 'STTIndo26' ssh -o StrictHostKeyChecking=no -p 2122 debian@116.206.199.251 "echo 'STTIndo26' | sudo -S tar -xzf /tmp/update_lalamove.tar.gz -C /var/www/dev.rasaconnect.com/public/.."

echo "Deployed successfully"
