# Sistema de Monitoreo de Red (MVP)

Sistema para monitorear servidores y dispositivos de red en tiempo real. Detecta caídas (Ping) y analiza rendimiento (SNMP) con alertas automáticas.

## 📋 Requisitos
*   **PHP 8.1+** (Extensiones requeridas: `php_snmp`, `php_mongodb`, `php_fileinfo`)
*   **Base de Datos:** MongoDB (Debe estar ejecutándose)
*   **Herramientas:** Composer, Node.js & NPM

## ⚙️ Instalación

1.  **Instalar dependencias**
    Descarga las librerías de Backend (Laravel, MongoDB) y Frontend (Tailwind, Vite):
    ```bash
    # Asegúrate de tener las extensiones PHP habilitadas (snmp, mongodb, fileinfo)
    composer install
    npm install
    ```

2.  **Configurar entorno**
    Copia el archivo de configuración y ajusta la conexión a la base de datos:
    ```bash
    cp .env.example .env
    ```
    *Abre el archivo `.env` y verifica estos valores:*
    ```ini
    DB_CONNECTION=mongodb
    DB_HOST=127.0.0.1
    DB_PORT=27017
    DB_DATABASE=Monitoreo
    ```

3.  **Inicializar sistema**
    Genera la clave, crea la base de datos con datos de prueba y compila los estilos:
    ```bash
    php artisan key:generate
    php artisan migrate:fresh --seed
    npm run build
    ```

## Ejecución (Requiere 2 Terminales)

Mantén estas dos terminales abiertas para que el sistema funcione:

**Terminal 1: Interfaz Web**
```bash
php artisan serve
# Para acceso en red local (LAN):
# php artisan serve --host=0.0.0.0 --port=8000
```

**Terminal 2 (Monitor):**
```bash
php artisan schedule:work
```

## Credenciales
*   **Email:** admin@monitor.com
*   **Password:** password