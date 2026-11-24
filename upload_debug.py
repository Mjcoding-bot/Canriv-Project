import ftplib
import io

wp_debug_php = """<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/wp-load.php';
echo "wp-load.php loaded successfully!<br>";
"""

ftp = ftplib.FTP('ftpupload.net', timeout=30)
ftp.login('if0_43500400', 'HvXbjs6wGuzA')
ftp.storbinary('STOR htdocs/wordpress/test_wp_load.php', io.BytesIO(wp_debug_php.encode('utf-8')))
ftp.quit()
print("test_wp_load.php uploaded successfully")
