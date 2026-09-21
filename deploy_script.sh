#!/bin/bash
FILES=(
    "app/Http/Controllers/CheckoutController.php"
    "app/Http/Controllers/Admin/WarehouseController.php"
    "app/Services/MasterSync/QadHubSyncService.php"
)

# Tar the files
tar -czf update.tar.gz "${FILES[@]}"

# Upload to tmp
sshpass -p 'STTIndo26' scp -P 2122 update.tar.gz debian@116.206.199.251:/tmp/

# Extract on server using sudo
sshpass -p 'STTIndo26' ssh -p 2122 debian@116.206.199.251 "echo 'STTIndo26' | sudo -S tar -xzf /tmp/update.tar.gz -C /var/www/dev.rasaconnect.com/"

echo "Deployed successfully"
