#!/usr/bin/env python3
"""
Upload menu images to Cloudinary for production.

Usage:
    CLOUDINARY_URL=cloudinary://key:secret@cloud_name python scripts/upload-menu-images.py

Requires:
    pip install cloudinary
"""
import os
import sys
from pathlib import Path

try:
    import cloudinary
    import cloudinary.uploader
except ImportError:
    print("cloudinary module not installed. Run: pip install cloudinary")
    sys.exit(1)


def main():
    img_dir = Path("public/images/menu")
    if not img_dir.is_dir():
        print(f"Directory {img_dir} not found. Skipping upload.")
        return

    # Cloudinary config is auto-read from CLOUDINARY_URL env var
    cloudinary.config()

    extensions = ("*.jpg", "*.jpeg", "*.png", "*.webp")
    uploaded = 0
    failed = 0

    for pattern in extensions:
        for img in sorted(img_dir.glob(pattern)):
            name = img.stem
            public_id = f"menu/{name}"
            print(f"Uploading {img.name}...")
            try:
                cloudinary.uploader.upload(
                    str(img),
                    public_id=public_id,
                    folder="pos-cafe",
                    use_filename=True,
                )
                uploaded += 1
            except Exception as e:
                print(f"Failed: {img.name} - {e}")
                failed += 1

    print(f"Done. Uploaded: {uploaded}, Failed: {failed}")


if __name__ == "__main__":
    main()
