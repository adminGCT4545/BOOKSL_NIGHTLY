#!/bin/bash

# Setup script for PostgreSQL live updates

# Check if PostgreSQL is running
pg_status=$(sudo service postgresql status | grep "active (running)")
if [ -z "$pg_status" ]; then
  echo "PostgreSQL is not running. Starting PostgreSQL..."
  sudo service postgresql start
  sleep 2
fi

# Load environment variables
if [ -f .env ]; then
  export $(grep -v '^#' .env | xargs)
elif [ -f backend/.env ]; then
  export $(grep -v '^#' backend/.env | xargs)
else
  echo "No .env file found. Using default PostgreSQL credentials."
  export PGHOST=localhost
  export PGUSER=your_postgres_username
  export PGPASSWORD=your_postgres_password
  export PGDATABASE=booksl_train
  export PGPORT=5432
fi

# Run the setup-live-updates.sql script
echo "Setting up PostgreSQL triggers and functions for live updates..."
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -U $PGUSER -d $PGDATABASE -p $PGPORT -f setup-live-updates.sql

# Check if the setup was successful
if [ $? -eq 0 ]; then
  echo "Live updates setup completed successfully!"
  echo "The dashboard will now receive real-time updates from the PostgreSQL database."
else
  echo "Error setting up live updates. Please check the PostgreSQL credentials and try again."
  exit 1
fi

echo "You can now start the backend server with: cd backend && node src/server.js"
echo "And the frontend with: npm run dev"
