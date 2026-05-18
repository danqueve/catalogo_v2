# 🛒 Catálogo Web Autogestionable

Catálogo de productos mobile-first con panel de administración y botón de compartir por WhatsApp. Diseño premium estilo iOS/Cupertino.

## Stack

- **Backend:** PHP puro (OOP) + PDO
- **Base de datos:** MySQL 8
- **Frontend:** HTML5 · CSS3 · Bootstrap 5 · Vanilla JS

## Características

- 📱 Diseño 100% Mobile-First (Cupertino/iOS)
- 🗂️ CRUD de categorías con imagen de portada
- 📦 CRUD de artículos con upload de imagen (JPG · PNG · WebP)
- 🖼️ Proporción 4:5 garantizada en todas las imágenes (`aspect-ratio` + `object-fit: cover`)
- 🔍 Lightbox nativo al tocar la imagen del producto
- 💳 Cuotas semanales y mensuales por producto
- 💬 Botón WhatsApp con mensaje pre-formateado (nombre + cuotas + link)
- 🔒 Login seguro para el administrador (CSRF · `password_hash` · sesiones HttpOnly)

## Instalación

### 1. Clonar el repositorio

```bash
git clone git@github.com:danqueve/catalogo_v2.git
cd catalogo_v2
```

### 2. Configurar la base de datos

Importar el esquema en phpMyAdmin (o por CLI):

```bash
mysql -u root -p < sql/schema.sql
```

Esto crea la base de datos `catalogo` con todas las tablas y el usuario admin por defecto.

### 3. Configurar credenciales

```bash
cp config/config.example.php config/config.php
```

Editar `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'catalogo');
define('DB_USER', 'root');
define('DB_PASS', 'tu_contraseña');

define('BASE_URL', 'http://localhost/catalogo_v2/public');

// Número WhatsApp del negocio (con código de país, sin +). Vacío = el vendedor elige el contacto.
define('WA_PHONE', '5491155557777');
```

### 4. Permisos de la carpeta de uploads

```bash
chmod 775 public/uploads/productos/
```

## Acceso

| URL | Descripción |
|---|---|
| `http://localhost/catalogo_v2/public/` | Catálogo público (vendedores) |
| `http://localhost/catalogo_v2/admin/login.php` | Panel de administración |

**Credenciales por defecto:**
- Usuario: `admin`
- Contraseña: `admin123`

> ⚠️ Cambiá la contraseña desde phpMyAdmin usando `password_hash()` luego de la instalación.

## Estructura del proyecto

```
catalogo_v2/
├── public/                  # Document root
│   ├── index.php            # Vista pública: grilla de categorías
│   ├── categoria.php        # Listado de productos por categoría
│   ├── assets/
│   │   ├── css/app.css      # Sistema de diseño Cupertino
│   │   └── js/app.js        # Lightbox, slug automático, previews
│   └── uploads/productos/   # Imágenes subidas (no versionadas)
├── admin/                   # Panel de administración
│   ├── login.php
│   ├── dashboard.php
│   ├── categorias.php       # CRUD categorías
│   ├── articulos.php        # CRUD artículos
│   └── partials/            # Header y footer del admin
├── src/                     # Lógica PHP (bloqueada al acceso web)
│   ├── Config/Database.php  # Singleton PDO
│   ├── Models/              # Categoria · Articulo · Usuario
│   ├── Helpers/             # Auth · Upload · Whatsapp
│   └── bootstrap.php
├── config/
│   ├── config.php           # Credenciales (no versionado)
│   └── config.example.php   # Plantilla de configuración
└── sql/
    └── schema.sql           # Esquema + seed de admin
```

## Seguridad

- Todos los queries usan **PDO prepared statements** (previene SQL injection)
- Formularios del admin protegidos con **token CSRF**
- Contraseñas hasheadas con **`password_hash(PASSWORD_DEFAULT)`**
- Sesiones con cookies **HttpOnly + SameSite=Lax**
- Validación de uploads: MIME real (`finfo`), extensión whitelist, tamaño máx. 3 MB
- Directorios `/src`, `/config` y `/sql` bloqueados vía `.htaccess`

## Licencia

MIT
