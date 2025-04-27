# BookSL-Train Dashboard

A comprehensive dashboard for the BookSL Train Management System that visualizes train operations data, including schedules, ticket sales, occupancy rates, and performance metrics.

![BookSL-Train Dashboard](https://via.placeholder.com/800x400?text=BookSL-Train+Dashboard)

## Overview

The BookSL-Train Dashboard provides real-time insights into train operations with interactive visualizations for:

- Train schedules and status monitoring
- Ticket sales and revenue analysis
- Occupancy and capacity management
- Performance metrics and delay tracking

## Prerequisites

Before you begin, ensure you have the following installed:

- [Node.js](https://nodejs.org/) (v18.0.0 or higher)
- npm (v9.0.0 or higher) or [yarn](https://yarnpkg.com/) (v1.22.0 or higher)
- [PostgreSQL](https://www.postgresql.org/) (v14.0 or higher)

## Installation

1. Clone the repository:

```bash
git clone https://github.com/adminGCT4545/BOOKSL_NIGHTLY.git
cd BookSL-Train-Dashboard
```

2. Install dependencies:

```bash
npm install
# or
yarn
```

3. Configure the database:

   a. Create a `.env` file in the root directory with your PostgreSQL credentials:

   ```
   PGHOST=localhost
   PGUSER=your_postgres_username
   PGPASSWORD=your_postgres_password
   PGDATABASE=booksl_train
   PGPORT=5432
   ```

   b. Ensure your PostgreSQL user has the correct password:

   ```bash
   # Connect to PostgreSQL as a superuser
   sudo -u postgres psql

   # Set the password for your PostgreSQL user
   ALTER USER your_postgres_username WITH PASSWORD 'your_postgres_password';
   
   # Exit PostgreSQL
   \q
   ```

   c. Run the database setup script:

   ```bash
   ./setup-database.sh
   ```

   This script will create the database and tables, and populate them with sample data.

## Running the Application

To start the development server:

```bash
npm run dev
# or
yarn dev
```

This will start the application in development mode. Open [http://localhost:5173](http://localhost:5173) in your browser to view the dashboard.

## Building for Production

To build the application for production:

```bash
npm run build
# or
yarn build
```

To preview the production build locally:

```bash
npm run preview
# or
yarn preview
```

## Project Structure

```
BookSL-Train-Dashboard/
├── public/               # Static assets
├── src/                  # Source files
│   ├── assets/           # Images and other assets
│   ├── components/       # React components
│   │   └── Dashboard.tsx # Main dashboard component
│   ├── services/         # Service modules
│   │   ├── dataService.ts # Data transformation service
│   │   └── dbService.ts   # Database connection service
│   ├── App.tsx           # Main application component
│   ├── main.tsx          # Application entry point
│   └── index.css         # Global styles
├── index.html            # HTML template
├── .env                  # Environment variables for database connection
├── setup-database.sql    # SQL script to create and populate database tables
├── setup-database.sh     # Shell script to set up the database
├── package.json          # Project dependencies and scripts
├── tsconfig.json         # TypeScript configuration
├── vite.config.ts        # Vite configuration
└── tailwind.config.js    # Tailwind CSS configuration
```

## Technologies Used

- [React](https://reactjs.org/) - UI library
- [TypeScript](https://www.typescriptlang.org/) - Type-safe JavaScript
- [Vite](https://vitejs.dev/) - Build tool and development server
- [Tailwind CSS](https://tailwindcss.com/) - Utility-first CSS framework
- [Recharts](https://recharts.org/) - Charting library for data visualization
- [PostgreSQL](https://www.postgresql.org/) - Relational database
- [node-postgres](https://node-postgres.com/) - PostgreSQL client for Node.js

## Features

- **Interactive Dashboard**: Filter and analyze data with interactive controls
- **Real-time Monitoring**: Track train schedules and delays
- **Revenue Analysis**: Visualize ticket sales and revenue by train and class
- **Occupancy Tracking**: Monitor train occupancy rates
- **Performance Metrics**: Track key performance indicators
- **PostgreSQL Integration**: Data stored and retrieved from a PostgreSQL database
- **Fallback Mechanism**: Graceful handling of database connection issues with fallback data

## Development

### Available Scripts

- `npm run dev` - Start the development server
- `npm run build` - Build for production
- `npm run lint` - Run ESLint to check code quality
- `npm run preview` - Preview the production build locally
- `./setup-database.sh` - Set up the PostgreSQL database

### Database Management

The application uses PostgreSQL to store train journey data. The database connection is managed by the `dbService.ts` module, which uses the node-postgres library to connect to the database.

Key database files:
- `.env` - Contains database connection parameters
- `setup-database.sql` - SQL script to create tables and insert sample data
- `setup-database.sh` - Shell script to automate database setup
- `src/services/dbService.ts` - Database connection service
- `src/services/dataService.ts` - Service to transform database data for the dashboard

If you need to modify the database schema or sample data, edit the `setup-database.sql` file and run the setup script again.

### PostgreSQL Connection Troubleshooting

If you encounter PostgreSQL connection issues, here are some common solutions:

1. **Password Authentication Issues**:
   - Ensure the password in your `.env` file matches the actual PostgreSQL user password
   - If you get "password authentication failed" errors, reset your PostgreSQL user password:
     ```bash
     # Connect to PostgreSQL as a superuser
     sudo -u postgres psql
     
     # Reset the password for your user
     ALTER USER your_postgres_username WITH PASSWORD 'your_postgres_password';
     ```

2. **Authentication Methods**:
   - PostgreSQL uses different authentication methods depending on how you connect:
     - **Peer Authentication** (Unix Socket): Used when connecting locally without specifying a host
     - **Password Authentication** (TCP/IP): Used when connecting with an explicit host
   - For PHP applications, always use password authentication by specifying the host (localhost)

3. **Connection Testing**:
   - Test your PostgreSQL connection using the provided test scripts:
     ```bash
     # Test connection using PHP
     php php_app/includes/test_connection.php
     
     # Comprehensive connection test
     php php_app/includes/postgres_connection_test.php
     ```

### Customization

The dashboard theme can be customized in the `tailwind.config.js` file. The application uses custom color variables for consistent theming:

```js
colors: {
  'dashboard-dark': '#1e2130',
  'dashboard-panel': '#282c3e',
  'dashboard-purple': '#7e57c2',
  'dashboard-blue': '#4e7fff',
  'dashboard-light-purple': '#b39ddb',
  'dashboard-text': '#e0e0e0',
  'dashboard-header': '#ffffff',
  'dashboard-subtext': '#9e9e9e',
}
```

## License

[MIT](LICENSE)
