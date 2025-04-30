# BookSL Train Dashboard - PHP Version

This is the PHP version of the BookSL Train Dashboard, a web application for monitoring and managing train operations, schedules, ticket sales, and fleet performance.

## Features

- **Dashboard**: Overview of key metrics and performance indicators
- **Train Schedules**: Detailed view of train schedules, routes, and performance
- **Ticket Sales**: Analysis of ticket sales, revenue, and passenger statistics
- **Train Fleet**: Management of train fleet performance and statistics
- **Remote Management**: Real-time monitoring of train operations

## Requirements

- PHP 7.4 or higher
- PostgreSQL 12 or higher
- PHP PDO Extension with PostgreSQL driver
- Web server (Apache, Nginx, etc.)

## Installation

1. Clone the repository or copy the files to your web server directory.

2. Create a PostgreSQL database using the provided SQL script:

```bash
# Run the setup script
./setup-database.sh
```

Alternatively, you can manually create the database and tables:

```bash
# Create the database
createdb booksl_train

# Import the schema and data
psql -d booksl_train -f setup-database.sql
```

3. Configure the database connection by editing the `.env` file:

```
PGHOST=localhost
PGUSER=postgres
PGPASSWORD=your_password
PGDATABASE=booksl_train
PGPORT=5432
```

4. Ensure your PostgreSQL user has the correct password:

```bash
# Connect to PostgreSQL as a superuser
sudo -u postgres psql

# Set the password for your PostgreSQL user
ALTER USER postgres WITH PASSWORD 'your_password';

# Exit PostgreSQL
\q
```

5. Make sure the web server has read/write permissions for the application directory.

## Running the Application

1. Start your web server (Apache, Nginx, etc.).

2. Access the application through your web browser:

```
http://localhost/php_app/
```

If you're using PHP's built-in web server for development:

```bash
# Navigate to the application directory
cd php_app

# Start the PHP development server
php -S localhost:8000
```

Then access the application at:

```
http://localhost:8000/
```

## Project Structure

- `includes/`: Core PHP files
  - `db_connection.php`: Database connection handling
  - `data_service.php`: Data processing and transformation
  - `layout.php`: Common layout template
- `js/`: JavaScript files
  - `dashboard.js`: Chart rendering and interactive elements
- `*.php`: Main application pages
  - `index.php`: Dashboard page
  - `train_schedules.php`: Train schedules page
  - `ticket_sales.php`: Ticket sales analysis
  - `train_fleet.php`: Train fleet management
  - `remote_management.php`: Real-time monitoring

## Database Schema

The application uses the following database tables:

- `trains`: Information about trains
- `train_schedules`: Train schedule information
- `train_journeys`: Journey details including revenue and occupancy

## Connecting to PostgreSQL

The application connects to PostgreSQL using PHP's PDO extension. The connection details are stored in the `.env` file and loaded by the application.

### PostgreSQL Authentication Methods

PostgreSQL uses different authentication methods depending on how you connect:

1. **Peer Authentication** (Unix Socket):
   - Used when connecting locally without specifying a host
   - Requires that your operating system username matches the PostgreSQL username
   - Example: `psql -U postgres`

2. **Password Authentication** (TCP/IP):
   - Used when connecting with an explicit host
   - Requires the correct password for the PostgreSQL user
   - Example: `psql -h localhost -U postgres`

For PHP applications, always use password authentication by specifying the host (localhost) in your connection parameters.

### Testing the Connection

You can test your PostgreSQL connection using the provided test scripts:

```bash
# Basic connection test
php includes/test_connection.php

# Comprehensive connection test
php includes/postgres_connection_test.php
```

### Troubleshooting Connection Issues

If you encounter "password authentication failed" errors:

1. Verify that the password in your `.env` file matches the actual PostgreSQL user password
2. Reset your PostgreSQL user password to match your configuration:

```bash
# Connect to PostgreSQL as a superuser
sudo -u postgres psql

# Reset the password for your user
ALTER USER postgres WITH PASSWORD 'your_password';
```

3. Ensure you're using password authentication by specifying the host in your connection parameters

## Customization

You can customize the application by:

1. Modifying the CSS styles in the `layout.php` file
2. Updating the JavaScript charts in `dashboard.js`
3. Adding new features by creating additional PHP files

## Troubleshooting

- **Database Connection Issues**: 
  - Ensure PostgreSQL is running and the connection details in `.env` are correct
  - Verify that the PostgreSQL user password matches what's in your `.env` file
  - Check PostgreSQL logs for authentication failures: `sudo tail -f /var/log/postgresql/postgresql-17-main.log`
  - Test the connection using the provided test scripts
- **Missing Data**: Check if the database tables were created and populated correctly
- **PHP Errors**: Enable error reporting in PHP for development to see detailed error messages

## License

This project is licensed under the MIT License.
