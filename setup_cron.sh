#!/bin/bash

# Tambahkan cron job untuk menjalankan script pengiriman email setiap 5 menit
(crontab -l 2>/dev/null; echo "*/5 * * * * php /project/sandbox/user-workspace/cron/send_email_notifications.php") | crontab -

echo "Cron job berhasil ditambahkan!"
echo "Script email notifications akan dijalankan setiap 5 menit."
