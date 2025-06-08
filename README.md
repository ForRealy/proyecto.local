# Pokemon Database Web Application

A dynamic web application for managing and visualizing Pokemon data, built with PHP, MySQL, and modern web technologies.

## Features

- **Data Management**
  - Web scraping of Pokemon data using Python and Selenium
  - CRUD operations for Pokemon records
  - Type management and relationships
  - Image handling and storage

- **Authentication & Security**
  - JWT-based authentication
  - Secure admin panel
  - Session management with cookies
  - Protected API endpoints

- **User Interface**
  - Responsive design using Bootstrap
  - Interactive data visualization with Chart.js
  - Search and filter functionality
  - Admin dashboard for data management

- **Internationalization**
  - Multi-language support (English and Spanish)
  - Language switching
  - Translation using gettext

## Technical Stack

- **Backend**
  - PHP 8.0+
  - MySQL 8.0+
  - Apache/Nginx web server
  - JWT for authentication
  - gettext for internationalization

- **Frontend**
  - HTML5, CSS3, JavaScript
  - Bootstrap 5
  - Chart.js for data visualization
  - Twig templating engine

- **Development Tools**
  - Python 3.8+ for web scraping
  - Selenium for browser automation
  - Composer for PHP dependencies

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/pokemon-database.git
   cd pokemon-database
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Python dependencies:
   ```bash
   cd scrap
   python -m venv venv
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   pip install -r requirements.txt
   ```

4. Configure the database:
   - Create a MySQL database named `pokemon_db`
   - Import the database schema from `database/schema.sql`
   - Update database credentials in `config/database.php`

5. Configure the web server:
   - Point the document root to the `public` directory
   - Ensure the `public/images` directory is writable
   - Enable URL rewriting (mod_rewrite for Apache)

6. Set up environment variables:
   - Copy `config/jwt_config.php.example` to `config/jwt_config.php`
   - Update the JWT secret key and other settings

7. Run the data scraper:
   ```bash
   cd scrap
   source venv/bin/activate  # On Windows: venv\Scripts\activate
   python insert_pokemon_data.py
   ```

## Usage

1. Access the application at `http://localhost/pokemon-database`
2. Default admin credentials:
   - Username: admin
   - Password: admin123

3. Available features:
   - Browse Pokemon list with search and filters
   - View detailed Pokemon information
   - Access admin panel for data management
   - View statistics and charts
   - Switch between languages

## Project Structure

```
pokemon-database/
├── api/                    # API endpoints
├── config/                 # Configuration files
├── database/              # Database schema and migrations
├── locale/                # Translation files
├── middleware/            # Authentication middleware
├── public/                # Public assets
│   ├── images/           # Pokemon images
│   └── index.php         # Front controller
├── scrap/                 # Python scraping scripts
├── vendor/                # Composer dependencies
└── views/                 # Twig templates
    ├── admin/            # Admin panel templates
    └── components/       # Reusable template components
```

## Security Considerations

- Change the default admin password
- Use a strong JWT secret key
- Enable HTTPS in production
- Keep dependencies updated
- Follow security best practices for file uploads

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Pokemon data sourced from [Pokemon Database](https://pokemondb.net)
- Icons and images from official Pokemon resources
- Built with open-source technologies

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
