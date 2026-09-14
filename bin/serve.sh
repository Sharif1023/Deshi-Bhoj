#!/bin/sh
cd "$(dirname "$0")/.." || exit 1
exec php -d upload_max_filesize=5M -d post_max_size=6M -S localhost:8000 -t public public/router.php
