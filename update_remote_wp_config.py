import ftplib
import io

ftp = ftplib.FTP('ftpupload.net', timeout=30)
ftp.login('if0_43500400', 'HvXbjs6wGuzA')
buf = io.BytesIO()
ftp.retrbinary('RETR htdocs/wordpress/wp-config.php', buf.write)
content = buf.getvalue().decode('utf-8')

content = content.replace("define( 'DB_NAME', 'Canriv' );", "define( 'DB_NAME', 'if0_43500400_canriv_wp' );")
content = content.replace("define( 'DB_USER', 'root' );", "define( 'DB_USER', 'if0_43500400' );")
content = content.replace("define( 'DB_PASSWORD', '' );", "define( 'DB_PASSWORD', 'HvXbjs6wGuzA' );")
content = content.replace("define( 'DB_HOST', 'localhost' );", "define( 'DB_HOST', 'sql311.infinityfree.com' );")

# Upload updated wp-config.php
upload_buf = io.BytesIO(content.encode('utf-8'))
ftp.storbinary('STOR htdocs/wordpress/wp-config.php', upload_buf)
ftp.quit()

print("wp-config.php successfully updated on remote server!")
