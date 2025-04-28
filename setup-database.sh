#!/bin/bash

# Script to set up the PostgreSQL database for BookSL Train Dashboard

# Check if PostgreSQL is installed
if ! command -v psql &> /dev/null; then
    echo "PostgreSQL is not installed. Please install PostgreSQL first."
    exit 1
fi

# Load environment variables from .env file
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
    echo "Loaded environment variables from .env file."
else
    echo "Error: .env file not found."
    exit 1
fi

# Check if required environment variables are set
if [ -z "$PGUSER" ] || [ -z "$PGHOST" ] || [ -z "$PGDATABASE" ]; then
    echo "Error: Required environment variables are not set in .env file."
    echo "Please make sure PGUSER, PGHOST, and PGDATABASE are defined."
    exit 1
fi

echo "Setting up PostgreSQL database for BookSL Train Dashboard..."

# Create database if it doesn't exist
echo "Creating database $PGDATABASE if it doesn't exist..."
psql -U $PGUSER -h $PGHOST -c "CREATE DATABASE $PGDATABASE;" 2>/dev/null || echo "Database already exists or couldn't be created."

# Run the SQL script to create tables and insert data
echo "Creating tables and inserting data..."
psql -U $PGUSER -h $PGHOST -d $PGDATABASE -f setup-database.sql

if [ $? -eq 0 ]; then
    echo "Database setup completed successfully!"
    echo "You can now run the application with: npm run dev"
else
    echo "Error: Failed to set up the database."
    echo "Please check your PostgreSQL connection settings in the .env file."
    exit 1
fi
