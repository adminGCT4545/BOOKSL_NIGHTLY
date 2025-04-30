# PostgreSQL Connection for Dashboard Backend

This document explains how to set up and use the PostgreSQL connection for the BookSL Train dashboard backend.

## Prerequisites

- PostgreSQL server running on localhost or accessible network
- PostgreSQL database named `booksl_train` (or as specified in your .env file)
- PostgreSQL user with appropriate permissions

## Configuration

1. Update the `.env` file with your PostgreSQL credentials:

```
PGHOST=localhost
PGUSER=your_postgres_username
PGPASSWORD=your_postgres_password
PGDATABASE=booksl_train
PGPORT=5432
```

Replace `your_postgres_username` and `your_postgres_password` with your actual PostgreSQL credentials.

## Database Setup

1. Run the database setup script to create the necessary tables and insert sample data:

```bash
./connect-postgres.sh
```

This script will:
- Connect to your PostgreSQL database using the credentials from the `.env` file
- Run the `setup-database.sql` script to create tables and insert sample data

## API Endpoints

The dashboard backend provides the following API endpoints:

### General Endpoints

- `GET /api/test` - Test if the backend server is running
- `POST /api/query` - Execute a custom SQL query (requires text and optional params in the request body)

### Dashboard-specific Endpoints

- `GET /api/dashboard/test` - Test if the dashboard API is working
- `GET /api/dashboard/trains` - Get all trains
- `GET /api/dashboard/journeys` - Get all train journeys with optional filtering
  - Query parameters:
    - `year` (optional) - Filter by year
    - `trainId` (optional) - Filter by train ID
    - `limit` (optional) - Limit the number of results (default: 100)
- `GET /api/dashboard/upcoming` - Get upcoming train departures
  - Query parameters:
    - `count` (optional) - Number of departures to return (default: 5)
- `GET /api/dashboard/stats` - Get dashboard statistics
- `GET /api/dashboard/years` - Get available years in the data

## Data Model

The database consists of the following tables:

### trains

- `train_id` (INTEGER, PRIMARY KEY) - Train ID
- `train_name` (VARCHAR) - Train name

### train_schedules

- `train_id` (INTEGER, PRIMARY KEY, REFERENCES trains) - Train ID
- `scheduled_time` (TIME) - Scheduled departure time
- `default_delay_minutes` (INTEGER) - Default delay in minutes

### train_journeys

- `journey_id` (SERIAL, PRIMARY KEY) - Journey ID
- `train_id` (INTEGER, REFERENCES trains) - Train ID
- `departure_city` (VARCHAR) - Departure city
- `arrival_city` (VARCHAR) - Arrival city
- `journey_date` (DATE) - Journey date
- `class` (VARCHAR) - Travel class (First, Second, Third)
- `total_seats` (INTEGER) - Total seats available
- `reserved_seats` (INTEGER) - Number of reserved seats
- `is_delayed` (BOOLEAN) - Whether the train is delayed
- `revenue` (DECIMAL) - Revenue generated from this journey

## Troubleshooting

If you encounter issues with the PostgreSQL connection:

1. Verify that your PostgreSQL server is running:
   ```bash
   sudo service postgresql status
   ```

2. Check that the database exists:
   ```bash
   psql -U your_postgres_username -l
   ```

3. Ensure your credentials in the `.env` file are correct

4. Check the backend logs for specific error messages:
   ```bash
   npm run start:backend
   ```

If the database connection fails, the backend will fall back to using mock data, so the dashboard will still function with limited data.
