# Ubermoldes

Pequeña aplicación web desarrollada en PHP orientada a la gestión de viajes tipo Uber, estructurada con vistas diferenciadas para clientes y conductores.

## Requisitos y Dependencias

Para ejecutar este proyecto de forma local, necesitas contar con lo siguiente:

* **PHP** (versión 7.4 o superior recomendada)
* **Extensión `php-mysql`** (necesaria para que PHP pueda comunicarse con bases de datos MySQL/MariaDB mediante PDO)
* **Servidor MySQL / MariaDB** (puedes instalarlo mediante XAMPP, paquetes nativos de tu distribución Linux, etc.)

---

## Instalación y Configuración

### 1. Instalación de PHP y dependencias en Linux (Ubuntu/Debian)

Si aún no tienes PHP o la extensión de MySQL instalada en tu sistema, puedes instalarla ejecutando:

```bash
sudo apt update
sudo apt install php php-mysql
```

### 2. Configuración de la Base de Datos

El proyecto asume una base de datos llamada `moldesgo`. Puedes crearla y estructurarla de la siguiente manera:

#### Usando phpMyAdmin (XAMPP):

1. Inicia el panel de control de XAMPP y asegúrate de que **Apache** y **MySQL** estén encendidos.
2. Abre tu navegador y entra a [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Dirígete a la pestaña **SQL** o crea una nueva base de datos llamada `moldesgo` con cotejamiento `utf8mb4_unicode_ci`.
4. Ejecuta el siguiente script SQL para crear las tablas requeridas:

```sql
CREATE DATABASE moldesgo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE moldesgo;

-- Tabla de usuarios (clientes y conductores)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'conductor') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de viajes
CREATE TABLE viajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    conductor_id INT DEFAULT NULL,
    origen_nombre VARCHAR(255) NOT NULL,
    origen_lat DECIMAL(10, 8) NOT NULL,
    origen_lng DECIMAL(11, 8) NOT NULL,
    destino_nombre VARCHAR(255) NOT NULL,
    destino_lat DECIMAL(10, 8) NOT NULL,
    destino_lng DECIMAL(11, 8) NOT NULL,
    distancia_km DECIMAL(6, 2) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    estado VARCHAR(50) DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (conductor_id) REFERENCES usuarios(id)
);
```

#### Credenciales de conexión:

Verifica el archivo `moldes/conexion.php` para asegurarte de que los datos de acceso a MySQL coincidan con los de tu entorno (por defecto `$host = "127.0.0.1"`, `$user = "root"`).

---

## Ejecución del Servidor

Puedes poner en marcha la aplicación utilizando el servidor web interno incluido en PHP:

1. Abre tu terminal y sitúate en la raíz del proyecto:
   ```bash
   cd ruta/a/ubermoldes
   ```
2. Inicia el servidor apuntando al directorio `moldes`:
   ```bash
   php -S localhost:8000 -t moldes
   ```
3. Abre tu navegador web y accede a las páginas principales:
   * **Login / Registro:** [http://localhost:8000/login.php](http://localhost:8000/login.php)
   * **Vista de Cliente:** [http://localhost:8000/cliente.php](http://localhost:8000/cliente.php)
   * **Vista de Conductor:** [http://localhost:8000/conductor.php](http://localhost:8000/conductor.php)
