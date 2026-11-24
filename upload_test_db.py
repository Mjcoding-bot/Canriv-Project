import ftplib
import io

test_php = """<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "PHP is working! <br>";

$mysqli = new mysqli('sql311.infinityfree.com', 'if0_43500400', 'HvXbjs6wGuzA', 'if0_43500400_canriv_wp');
if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
echo "Database connection SUCCESSFUL! <br>";
$res = $mysqli->query("SELECT option_name, option_value FROM wp_options WHERE option_name IN ('siteurl', 'home')");
while ($row = $res->fetch_assoc()) {
    echo $row['option_name'] . ' = ' . $row['option_value'] . '<br>';
}
"""

ftp = ftplib.FTP('ftpupload.net', timeout=30)
ftp.login('if0_43500400', 'HvXbjs6wGuzA')
ftp.storbinary('STOR htdocs/wordpress/test_db.php', io.BytesIO(test_php.encode('utf-8')))
ftp.quit()
print('test_db.php uploaded successfully')
