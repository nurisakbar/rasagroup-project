#!/bin/bash
FILES=(
    "app/Models/OrderItem.php"
    "resources/views/buyer/orders/show.blade.php"
    "resources/views/buyer/orders/invoice.blade.php"
)

# Tar the files
tar -czf update_invoice.tar.gz "${FILES[@]}"

# Upload to tmp
sshpass -p 'STTIndo26' scp -P 2122 update_invoice.tar.gz debian@116.206.199.251:/tmp/

# Extract on server using sudo
sshpass -p 'STTIndo26' ssh -p 2122 debian@116.206.199.251 "echo 'STTIndo26' | sudo -S tar -xzf /tmp/update_invoice.tar.gz -C /var/www/dev.rasaconnect.com/"

echo "Deployed successfully"
