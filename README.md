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


## Configuración

Hay dos archivos `.env`:
- El de la **raíz** (`.env`) lo lee Docker: base de datos, carpeta de archivos y tamaño máximo de subida.
- El de **`src`** (`src/.env`) lo lee Laravel: idioma, zona horaria, espacio por defecto, etc.

### Tamaño máximo de un archivo

Se cambia en **un solo sitio**, en el `.env` de la raíz:
```
RYNKEEP_TAMANO_MAXIMO_MB=100
```
Docker se lo pasa a Nginx, a PHP y a la aplicación, así que no hay que tocar nada más. Para aplicar el cambio:
```bash
docker compose up -d --force-recreate
```
No se pierde ningún dato. Si no pones la variable, se usan 100 MB.

### Registro de usuarios

Por defecto está **cerrado**: solo el administrador crea cuentas, desde el panel de usuarios. Para que cualquiera pueda crearse una cuenta, en `src/.env`:
```
RYNKEEP_REGISTRO_ABIERTO=true
```
Aparece el enlace "Crear cuenta" en el login. Las cuentas nuevas no son administradoras y reciben el espacio por defecto (`RYNKEEP_LIMITE_DEFECTO_GB`). Con el registro cerrado, la dirección `/register` no existe (da error 404). Tras cambiarlo:
```bash
docker compose exec app php artisan optimize:clear
```



### Papelera

Los archivos que se eliminan van a la papelera. Pasados unos días se borran para siempre, solos, cada noche a las 00:00 (en la zona horaria de `APP_TIMEZONE`). Los días se ponen en `src/.env`:
```
RYNKEEP_DIAS_PAPELERA=30
```
Con `0`, nada se borra solo: los archivos se quedan en la papelera hasta que su dueño los elimine a mano. En la papelera, cada archivo muestra la fecha en la que se borrará.

Esto lo hace un contenedor propio (`tareas`) que se levanta con los demás. Para ejecutar el vaciado a mano, sin esperar a la noche:
```bash
docker compose exec app php artisan rynkeep:vaciar-papelera
```


## Idiomas

RynKeep viene en **español** e **inglés**. Cada usuario elige el suyo en su perfil. Si no elige ninguno, se usa el del sistema, que se cambia en `src/.env`:

```
APP_LOCALE=es
APP_FALLBACK_LOCALE=es
```

### Añadir un idioma nuevo

Las traducciones se escriben a mano, para que quien instale RynKeep controle cada palabra. No hay que tocar código ni apuntar el idioma en ningún sitio: el sistema detecta solo los que hay. Como ejemplo, vamos a añadir francés (`fr`).

**1. Copia los dos archivos de inglés**, que son la plantilla:
```bash
cp src/lang/en.json src/lang/fr.json
cp -r src/lang/en src/lang/fr
```

**2. Traduce `src/lang/fr.json`.** Es una lista con el texto original a la izquierda y la traducción a la derecha. Solo se cambia la parte de la **derecha**:
```json
{
    "_idioma": "Français",
    "Papelera": "Corbeille",
    "Restaurar": "Restaurer"
}
```
- La línea `"_idioma"` es el nombre que saldrá en el selector. **Cámbiala siempre**: al copiar `en.json` viene con `"English"`, y si no la cambias saldrá un segundo "English" repetido en el selector. Si la borras, el idioma no aparece.
- Los trozos que empiezan por dos puntos, como `:fecha` o `:nombre`, se dejan tal cual. El sistema los rellena solo. Puedes moverlos de sitio dentro de la frase.

**3. Traduce los 4 archivos de `src/lang/fr/`.** Tienen el mismo formato: se cambia el texto de la derecha. Son los mensajes de error de los formularios (`validation.php`), del acceso (`auth.php`), de contraseñas (`passwords.php`) y de paginación (`pagination.php`).

**4. Limpia la caché y recarga:**
```bash
docker compose exec app php artisan optimize:clear
```

El idioma nuevo aparece en el selector del perfil. Para quitarlo, basta con borrar `fr.json`.

Un idioma a medias no rompe nada: las frases que falten salen en español. Aun así, conviene revisar que no quede ninguna sin traducir antes de dejarlo disponible.

