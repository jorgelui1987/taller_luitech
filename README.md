# Taller Luitech - CRM para Tienda de Celulares

Sistema CRM multi-tenant para gestión de taller de reparación de celulares, inventario, ventas y más.

## 🚀 Características

- **Multi-tenant:** Soporte para múltiples tiendas con un solo panel SuperAdmin
- **Reparaciones:** Gestión completa de órdenes de reparación con firma digital y fotos
- **Inventario:** Control de stock, productos, alertas de stock bajo
- **Ventas:** Registro de ventas con generación de tickets
- **Clientes:** Base de datos de clientes con historial
- **WhatsApp:** Notificaciones automáticas a clientes
- **Reportes:** Reportes financieros y de gestión
- **Roles:** Admin, Vendedor, Técnico

## 🛠️ Stack Tecnológico

- **Backend:** Laravel 10, PHP 8.3
- **Frontend:** Bootstrap 5, Chart.js, Font Awesome
- **Base de datos:** MySQL 8.0
- **Servidor:** Apache
- **Contenedor:** Docker / Docker Compose
- **PDF:** Dompdf
- **Excel:** Laravel Excel

## 📋 Requisitos

- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js (opcional, para assets)
- Docker (opcional, para despliegue)

## 🔧 Instalación Local

```bash
# Clonar el repositorio
git clone https://github.com/jorgelui1987/taller_luitech.git
cd taller_luitech

# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Configurar base de datos en .env
# DB_DATABASE=luitech
# DB_USERNAME=root
# DB_PASSWORD=

# Ejecutar migraciones
php artisan migrate --seed

# Crear enlace de storage
php artisan storage:link

# Iniciar servidor
php artisan serve
```

## 🐳 Despliegue con Docker

```bash
# Construir y ejecutar
docker-compose up -d --build

# O usando variables de entorno personalizadas
docker-compose up -d --build -e DB_PASSWORD=tu_password
```

## ☁️ Despliegue en Producción (Railway / Dokploy)

1. Conecta el repositorio a la plataforma
2. Configura las siguientes variables de entorno:

```env
APP_KEY=base64:...(generar con php artisan key:generate)
APP_URL=https://tudominio.com
DB_HOST=host_de_tu_base_de_datos
DB_DATABASE=luitech
DB_USERNAME=usuario
DB_PASSWORD=contraseña
```

3. La aplicación se desplegará automáticamente con migraciones y optimizaciones

## 📁 Estructura del Proyecto

```
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controladores
│   │   └── Middleware/       # Middleware (tenant, roles)
│   ├── Models/               # Modelos Eloquent
│   └── Helpers/              # Helpers (WhatsApp, etc.)
├── config/                   # Configuración de Laravel
├── database/
│   ├── migrations/           # Migraciones
│   └── seeders/              # Seeders
├── docker/                   # Archivos Docker
├── resources/views/          # Vistas Blade
├── routes/                   # Rutas web/api
├── storage/                  # Almacenamiento
└── public/                   # Punto de entrada
```

## 📄 Licencia

MIT