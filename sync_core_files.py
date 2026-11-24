import ftplib
import os
import time

def upload_missing_files():
    ftp = ftplib.FTP('ftpupload.net', timeout=60)
    ftp.login('if0_43500400', 'HvXbjs6wGuzA')
    
    local_base = r'C:\xampp\htdocs\Canriv\wordpress'
    
    # We will upload wp-includes and wp-admin to ensure complete integrity
    folders_to_sync = ['wp-includes', 'wp-admin', 'wp-content/plugins/canriv-store']
    
    for rel_folder in folders_to_sync:
        l_dir = os.path.join(local_base, rel_folder.replace('/', '\\'))
        r_dir = f"htdocs/{rel_folder}"
        
        print(f"=== Syncing {rel_folder} ===")
        sync_dir(ftp, l_dir, r_dir)
        
    ftp.quit()
    print("ALL CORE DIRECTORIES SYNCED!")

def sync_dir(ftp, l_dir, r_dir):
    try:
        remote_items = ftp.nlst(r_dir)
        # nlst sometimes returns full paths or basenames
        remote_names = set(os.path.basename(p) for p in remote_items)
    except Exception:
        try:
            ftp.mkd(r_dir)
        except Exception:
            pass
        remote_names = set()

    for item in os.listdir(l_dir):
        l_path = os.path.join(l_dir, item)
        r_path = f"{r_dir}/{item}"
        
        if os.path.isdir(l_path):
            sync_dir(ftp, l_path, r_path)
        elif os.path.isfile(l_path):
            if item not in remote_names:
                try:
                    with open(l_path, 'rb') as f:
                        ftp.storbinary(f"STOR {r_path}", f)
                    print(f"Uploaded: {r_path}")
                except Exception as e:
                    print(f"Retry upload {r_path} due to: {e}")
                    time.sleep(1)
                    try:
                        with open(l_path, 'rb') as f:
                            ftp.storbinary(f"STOR {r_path}", f)
                    except Exception as e2:
                        print(f"Failed {r_path}: {e2}")

if __name__ == '__main__':
    upload_missing_files()
