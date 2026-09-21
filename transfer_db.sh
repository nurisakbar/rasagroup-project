#!/bin/bash
echo "Dumping DB from prod..."
sshpass -p 'STTIndo26' ssh -p 2122 debian@116.206.199.219 "echo 'STTIndo26' | sudo -S mysqldump -u root -pRasaMant4p123@\! rasaprod > /tmp/rasaprod_dump.sql"

echo "Downloading DB to local..."
sshpass -p 'STTIndo26' scp -P 2122 debian@116.206.199.219:/tmp/rasaprod_dump.sql /tmp/rasaprod_dump.sql

echo "Uploading DB to dev..."
sshpass -p 'STTIndo26' scp -P 2122 /tmp/rasaprod_dump.sql debian@116.206.199.251:/tmp/rasaprod_dump.sql

echo "Creating DB on dev..."
sshpass -p 'STTIndo26' ssh -p 2122 debian@116.206.199.251 "echo 'STTIndo26' | sudo -S mysql -u root -pSTTIndo26 -e 'CREATE DATABASE IF NOT EXISTS rasaprod_dev_new2;'"

echo "Importing DB to dev..."
sshpass -p 'STTIndo26' ssh -p 2122 debian@116.206.199.251 "echo 'STTIndo26' | sudo -S mysql -u root -pSTTIndo26 rasaprod_dev_new2 < /tmp/rasaprod_dump.sql"

echo "Updating .env on dev..."
sshpass -p 'STTIndo26' ssh -p 2122 debian@116.206.199.251 "echo 'STTIndo26' | sudo -S sed -i 's/DB_DATABASE=.*/DB_DATABASE=rasaprod_dev_new2/g' /var/www/dev.rasaconnect.com/.env"
sshpass -p 'STTIndo26' ssh -p 2122 debian@116.206.199.251 "cd /var/www/dev.rasaconnect.com && echo 'STTIndo26' | sudo -S php artisan optimize:clear"

echo "Done!"
