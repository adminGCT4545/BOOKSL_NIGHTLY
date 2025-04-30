# Dashboard Backend for BookSL Train Management System

This document provides an overview of the dashboard backend implementation that connects to a PostgreSQL database.

## Overview

The dashboard backend provides API endpoints for the BookSL Train Management System dashboard. It connects to a PostgreSQL database to fetch train data, schedules, and statistics.

## Features

- PostgreSQL database connection with fallback to mock data if connection fails
- RESTful API endpoints for dashboard data
- Data transformation for frontend consumption
- Error handling and logging

## Setup

1. Configure PostgreSQL credentials in the `.env` file:
   ```
   PGHOST=localhost
   PGUSER=your_postgres_username
   PGPASSWORD=your_postgres_password
   PGDATABASE=booksl_train
   PGPORT=5432
   ```

2. Run the database setup script:
   ```bash
   ./connect-postgres.sh
   ```

3. Start the backend server:
   ```bash
   cd backend
   node server.js
   ```

4. Start the frontend development server:
   ```bash
   npm run dev
   ```

## API Endpoints

The backend provides the following API endpoints:

### Dashboard Endpoints

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

### General Endpoints

- `GET /api/test` - Test if the backend server is running
- `POST /api/query` - Execute a custom SQL query (requires text and optional params in the request body)

## Database Schema

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

## Implementation Details

### Backend Structure

- `backend/server.js` - Main server file with Express setup and PostgreSQL connection
- `backend/routes/dashboard.js` - Dashboard API routes
- `backend/models/trainModel.js` - Database queries and data transformation

### Frontend Integration

- `src/services/dataService.ts` - Service for fetching and transforming data from the backend
- `src/services/dbService.ts` - Service for making database queries through the backend API
- `vite.config.ts` - Proxy configuration for API requests

## Troubleshooting

If you encounter issues with the PostgreSQL connection:

1. Verify that your PostgreSQL server is running
2. Check that the database exists
3. Ensure your credentials in the `.env` file are correct
4. Check the backend logs for specific error messages

If the database connection fails, the backend will fall back to using mock data, so the dashboard will still function with limited data.
