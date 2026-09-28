# VPS Isolated Deployment Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Deploy this catalog on its own domain without changing files, routing, or configuration used by other VPS sites.

**Architecture:** Keep the Git checkout outside the web root in a domain-specific directory. Point only this domain's Apache virtual host to the repository's `public/` directory, while its sibling `admin/`, `src/`, and `config/` directories remain inaccessible from HTTP. Store production credentials in the ignored `config/config.php` file on the VPS.

**Tech Stack:** PHP, Apache with VirtualHost, Git, rsync (optional), MySQL.

---

### Task 1: Publish the reviewed visual change

**Files:**
- Modify: `public/assets/css/app.css:1498`
- Create: `docs/plans/2026-09-28-vps-isolated-deployment.md`

**Step 1: Verify the staged patch is limited to the intended files**

Run: `git diff --cached --check && git diff --cached --name-only`

Expected: no whitespace errors; only the stylesheet and this plan are listed.

**Step 2: Commit and push**

Run: `git commit -m "style: update catalog header color" && git push origin main`

Expected: GitHub `main` advances with no unrelated local changes staged.

### Task 2: Create an isolated application checkout on the VPS

**Files:**
- Create on VPS: `/var/www/imperiocomercial/catalogo/config/config.php`
- Create on VPS: `/var/www/imperiocomercial/catalogo/public/uploads/productos/`
- Create on VPS: `/var/www/imperiocomercial/catalogo/public/assets/img/promociones/`

**Step 1: Clone outside any shared `public_html` directory**

Run: `sudo install -d -o www-data -g www-data /var/www/imperiocomercial && sudo -u www-data git clone https://github.com/danqueve/catalogo_v2.git /var/www/imperiocomercial/catalogo`

Expected: the repository lives only under `/var/www/imperiocomercial/catalogo`.

**Step 2: Add production configuration without committing it**

Run: `sudo -u www-data cp /var/www/imperiocomercial/catalogo/config/config.example.php /var/www/imperiocomercial/catalogo/config/config.php`

Expected: `config/config.php` contains production database credentials, the production `BASE_URL`, and paths under this catalog directory; it remains ignored by Git.

**Step 3: Create writable media directories**

Run: `sudo install -d -o www-data -g www-data -m 775 /var/www/imperiocomercial/catalogo/public/uploads/productos /var/www/imperiocomercial/catalogo/public/assets/img/promociones`

Expected: PHP can write uploads and promotion images for this site only.

### Task 3: Isolate the domain in Apache

**Files:**
- Create on VPS: `/etc/apache2/sites-available/imperiocomercial.com.ar.conf`

**Step 1: Create a domain-specific virtual host**

Use this configuration, replacing the domain names only if the intended production domain differs:

```apache
<VirtualHost *:80>
    ServerName imperiocomercial.com.ar
    ServerAlias www.imperiocomercial.com.ar
    DocumentRoot /var/www/imperiocomercial/catalogo/public

    <Directory /var/www/imperiocomercial/catalogo/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    Alias /admin /var/www/imperiocomercial/catalogo/admin
    <Directory /var/www/imperiocomercial/catalogo/admin>
        Options -Indexes +FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/imperiocomercial-error.log
    CustomLog ${APACHE_LOG_DIR}/imperiocomercial-access.log combined
</VirtualHost>
```

**Step 2: Validate before enabling**

Run: `sudo a2ensite imperiocomercial.com.ar.conf && sudo apachectl configtest`

Expected: `Syntax OK`. Do not reload Apache if validation fails.

**Step 3: Reload only after validation**

Run: `sudo systemctl reload apache2 && curl -I -H "Host: imperiocomercial.com.ar" http://127.0.0.1/`

Expected: the new domain responds successfully; existing virtual hosts remain unchanged because no shared document root or default-site file was edited.

### Task 4: Make future updates safe

**Files:**
- Create on VPS: `/usr/local/sbin/deploy-imperiocomercial`

**Step 1: Use a site-scoped deploy script**

```bash
#!/usr/bin/env bash
set -euo pipefail
cd /var/www/imperiocomercial/catalogo
git pull --ff-only origin main
```

**Step 2: Verify the application after each update**

Run: `curl -fsS https://imperiocomercial.com.ar/ >/dev/null && curl -fsS https://imperiocomercial.com.ar/admin/login.php >/dev/null`

Expected: both catalog and administrator login load, while uploads and `config/config.php` persist because they are ignored and never replaced.

**Step 3: Commit deployment documentation only if it changes**

Run: `git status --short`

Expected: the VPS checkout is clean after deployment; no server credentials, uploads, or configuration enter Git.
