import os
from PIL import Image

ads_dir = r"c:\xampp\htdocs\ELSAJANA2\magaies\uploads\ads"
for fname in os.listdir(ads_dir):
    fpath = os.path.join(ads_dir, fname)
    if os.path.isfile(fpath):
        try:
            im = Image.open(fpath)
            print(f"{fname}: {im.size}, {im.format}")
        except Exception as e:
            print(f"{fname}: {e}")
