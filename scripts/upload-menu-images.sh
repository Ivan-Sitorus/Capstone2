#!/usr/bin/env bash
# Upload menu images to Cloudinary for production
# Usage: CLOUDINARY_URL=cloudinary://key:secret@cloud_name ./scripts/upload-menu-images.sh
set -euo pipefail

IMG_DIR="public/images/menu"
if [ ! -d "$IMG_DIR" ]; then
    echo "Directory $IMG_DIR not found. Skipping upload."
    exit 0
fi

for img in "$IMG_DIR"/*.{jpg,jpeg,png,webp}; do
    [ -f "$img" ] || continue
    filename=$(basename "$img")
    name="${filename%.*}"
    echo "Uploading $filename..."
    cld uploader upload "$img" public_id="menu/$name" folder="pos-cafe" --use_filename || echo "Failed: $filename"
done

echo "Done."
