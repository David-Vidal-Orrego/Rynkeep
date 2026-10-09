# Rynkeep

Plataforma de almacenamiento en la nube y gestión de archivos con Laravel, Nginx y MySQL, contenerizada con Docker.


## Arquitectura e Infraestructura

El entorno de desarrollo se ejecuta mediante Docker Compose, cada servicio en contenedores diferentes:

- Servidor Web: Nginx escuchando en el puerto `8080`.
- Backend: PHP-FPM 8.2 con Composer, extensiones PDO MySQL y utilidades del sistema.
- Base de Datos: MySQL 8.0 en el puerto `3306`.


## Requisitos Previos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows / macOS) o Docker Engine + Docker Compose plugin (Linux).
- [Git](https://git-scm.com/).


## Despliegue y Puesta en Marcha

Sigue estos pasos para clonar y ejecutar el entorno desde cero:

### 1. Clonar el repositorio
```bash
git clone https://github.com/David-Vidal-Orrego/Rynkeep.git
cd Rynkeep
```

### 2. Configurar variables de entorno
Copia la plantilla base al archivo de entorno definitivo:
```bash
cp src/.env.example src/.env
```
Con eso tienes el archivo de las variables de entorno y se deben sustituir por las que vayas a poner en la base de datos o otras variables que puedas querer cambiar

### 3. Levantar los contenedores
Construye las imágenes e inicia los servicios:
```bash
docker compose up -d --build
```

### 4. Configurar Laravel
Ejecuta la instalación de dependencias, genera la clave y migra las tablas:
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app chmod -R 777 storage bootstrap/cache
```

### 5. Acceso
Aplicación web: http://localhost:8080
