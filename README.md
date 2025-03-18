# Instalación y Configuración del Proyecto de Página Web Dinámica en PHP
Este documento explica cómo configurar el entorno y ejecutar la aplicación web dinámica que incluye scraping de datos, autenticación JWT, internacionalización y un panel de administración.
## 📋 Requisitos Previos
- **PHP 8.0+** (con extensiones: `pdo_mysql`, `gettext`, `mbstring`, `json`)
- **Composer** (gestor de dependencias PHP)
- **MySQL 5.7+** o MariaDB
- **Python 3.8+** y `pip` (para el script de scraping)
- **Node.js** (opcional, si se requieren assets compilados)
- **Git**
- **Servidor Web** (Apache o Nginx)
- **Selenium WebDriver** y navegador compatible (ej: Chrome + ChromeDriver)
## 🚀 Pasos de Instalación
`composer install`
```
-- Crear la tabla Pokémon
CREATE TABLE Pokemon (
    Number INT PRIMARY KEY,
    Name VARCHAR(50) NOT NULL,
    ImagePath VARCHAR(255) NOT NULL
);

-- Crear la tabla de Tipos
CREATE TABLE Types (
    TypeID INT AUTO_INCREMENT PRIMARY KEY,
    TypeName VARCHAR(20) NOT NULL UNIQUE
);

-- Crear la tabla de relación Pokémon-Tipos
CREATE TABLE PokemonTypes (
    PokemonNumber INT NOT NULL,
    TypeID INT NOT NULL,
    PRIMARY KEY (PokemonNumber, TypeID),
    FOREIGN KEY (PokemonNumber) REFERENCES Pokemon(Number),
    FOREIGN KEY (TypeID) REFERENCES Types(TypeID)
);
```
