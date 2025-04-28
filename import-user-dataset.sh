#!/bin/bash

# Script to import the User dataset into PostgreSQL database for BookSL Train Dashboard

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
    # Default values if .env file doesn't exist
    export PGHOST=localhost
    export PGUSER=postgres
    export PGPASSWORD=postgres
    export PGDATABASE=booksl_train
    export PGPORT=5432
    echo "Using default PostgreSQL connection parameters."
fi

# Check if required environment variables are set
if [ -z "$PGUSER" ] || [ -z "$PGHOST" ] || [ -z "$PGDATABASE" ]; then
    echo "Error: Required environment variables are not set."
    echo "Please make sure PGUSER, PGHOST, and PGDATABASE are defined."
    exit 1
fi

echo "Importing User dataset into PostgreSQL database..."
echo "Host: $PGHOST"
echo "User: $PGUSER"
echo "Database: $PGDATABASE"
echo "Port: $PGPORT"
echo ""

# Run the SQL script to create the passengers table and insert data
echo "Creating passengers table and inserting data..."
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -U $PGUSER -d $PGDATABASE -f DataSets/User_dataset.sql

if [ $? -eq 0 ]; then
    echo "User dataset imported successfully!"
    
    # Count the number of records imported
    echo "Verifying import..."
    PGPASSWORD=$PGPASSWORD psql -h $PGHOST -U $PGUSER -d $PGDATABASE -c "SELECT COUNT(*) FROM passengers;"
    
    echo ""
    echo "To generate the full 1000 records dataset, uncomment and run the PL/pgSQL code at the end of the User_dataset.sql file."
else
    echo "Error: Failed to import the User dataset."
    echo "Please check your PostgreSQL connection settings."
    exit 1
fi
