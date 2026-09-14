@echo off
cd /d "%~dp0.."
php -d upload_max_filesize=5M -d post_max_size=6M -S localhost:8000 -t public public/router.php
