#!/bin/bash
# Script de deploy para VPS — sincroniza public/ al document root de Apache.
# Uso: bash deploy.sh  (desde /home/c2881399/public_html)
set -e

DEPLOY_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$DEPLOY_DIR"

echo "→ Pulling latest code..."
git pull origin main

echo "→ Syncing public/ to web root..."
rsync -av --exclude='uploads/' public/ "$DEPLOY_DIR/"

echo "→ Fixing bootstrap paths for root-level deployment..."
for f in index.php categoria.php producto.php consulta.php; do
  [ -f "$DEPLOY_DIR/$f" ] && sed -i "s|__DIR__ . '/../src/bootstrap.php'|__DIR__ . '/src/bootstrap.php'|g" "$DEPLOY_DIR/$f"
done

echo "✓ Deploy complete."
