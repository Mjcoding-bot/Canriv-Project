import ftplib
import io

ftp = ftplib.FTP('ftpupload.net', timeout=30)
ftp.login('if0_43500400', 'HvXbjs6wGuzA')
hello_content = b"<?php echo 'HELLO CANRIV LIVE!'; ?>"
ftp.storbinary('STOR htdocs/hello.php', io.BytesIO(hello_content))
ftp.quit()
print("Uploaded hello.php successfully!")
