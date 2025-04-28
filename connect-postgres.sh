#!/bin/bash

# Script to connect to PostgreSQL using password authentication

# Load environment variables from .env file if it exists
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

echo "Connecting to PostgreSQL..."
echo "Host: $PGHOST"
echo "User: $PGUSER"
echo "Database: $PGDATABASE"
echo "Port: $PGPORT"
echo ""

# Connect to PostgreSQL using password authentication
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -U $PGUSER -d $PGDATABASE

# Note: This script uses password authentication by explicitly specifying the host (-h)
# This avoids the peer authentication method that would be used for local connections
