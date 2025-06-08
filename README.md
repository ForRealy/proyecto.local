 # Pokemon Database

Una aplicación web dinámica para gestionar y visualizar datos de Pokémon, implementada con PHP, Bootstrap, y Twig.

## Características Implementadas

- 🎨 **Frontend Moderno**
  - Diseño responsive con Bootstrap 5
  - Interfaz de usuario intuitiva y profesional
  - Gráficos interactivos con Chart.js
  - Sistema de plantillas Twig

- 🔐 **Sistema de Autenticación Seguro**
  - Autenticación mediante JWT
  - Protección de rutas de administración
  - Sesiones persistentes con tokens de refresco
  - Panel de administración protegido

- 🌍 **Internacionalización**
  - Soporte para español e inglés
  - Selector de idiomas con banderas
  - Traducciones completas de la interfaz
  - Sistema de traducciones basado en gettext

- 📊 **Gestión de Datos**
  - Panel de administración completo
  - CRUD de Pokémon
  - Estadísticas y gráficos
  - Filtros y búsqueda

- 🐍 **Scraping de Datos**
  - Scripts de Python con Selenium
  - Extracción automática de datos
  - Almacenamiento estructurado en base de datos

## Requisitos del Sistema

- PHP 8.0 o superior
- MySQL 5.7 o superior
- Python 3.8+ (para scripts de scraping)
- Apache/Nginx con mod_rewrite habilitado
- Extensiones PHP:
  - gettext
  - pdo_mysql
  - json
  - session

## Instalación

1. Clona el repositorio:
   ```bash
   git clone [URL_DEL_REPOSITORIO]
   cd pokemon-database
   ```

2. Instala las dependencias de PHP:
   ```bash
   composer install
   ```

3. Configura la base de datos:
   - Crea una base de datos MySQL
   - Importa el esquema desde `database/schema.sql`
   - Copia `config/database.php.example` a `config/database.php`
   - Actualiza las credenciales de la base de datos

4. Configura el servidor web:
   - Apunta el document root a la carpeta `public`
   - Asegúrate de que `public/images` sea escribible
   - Habilita mod_rewrite (Apache) o configura las reglas de reescritura (Nginx)

5. Configura las variables de entorno:
   - Copia `config/jwt_config.php.example` a `config/jwt_config.php`
   - Actualiza la clave secreta JWT y otras configuraciones

6. Ejecuta el script de scraping:
   ```bash
   cd scrap
   source venv/bin/activate  # En Windows: venv\Scripts\activate
   python insert_pokemon_data.py
   ```

## Uso

1. Accede a la aplicación en `http://localhost/pokemon-database`
2. Credenciales de administrador por defecto:
   - Usuario: admin
   - Contraseña: admin123

3. Funcionalidades disponibles:
   - Navegar por la lista de Pokémon
   - Buscar y filtrar Pokémon
   - Ver estadísticas y gráficos
   - Gestionar datos desde el panel de administración
   - Cambiar entre idiomas (español/inglés)

## Estructura del Proyecto

```
pokemon-database/
├── api/                    # Endpoints de la API
├── config/                 # Archivos de configuración
├── database/              # Esquema y migraciones
├── locale/                # Archivos de traducción
├── middleware/            # Middleware de autenticación
├── public/                # Archivos públicos
│   ├── images/           # Imágenes de Pokémon
│   └── index.php         # Controlador frontal
├── scrap/                 # Scripts de Python
├── vendor/                # Dependencias de Composer
└── views/                 # Plantillas Twig
    ├── admin/            # Plantillas del panel de administración
    └── components/       # Componentes reutilizables
```

## Seguridad

- Cambia la contraseña de administrador por defecto
- Usa una clave JWT segura en producción
- Habilita HTTPS en producción
- Mantén las dependencias actualizadas
- Sigue las mejores prácticas de seguridad para subida de archivos

## Contribuir

1. Haz fork del repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

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
