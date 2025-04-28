# PostgreSQL Connection Guide

This document provides information about connecting to the PostgreSQL database for the BookSL Train Dashboard application.

## Connection Issue

If you see an error message like this:

```
connection failed: connection to server at "127.0.0.1", port 5432 failed: FATAL: password authentication failed for user "postgres"
```

This error occurs because of how PostgreSQL handles authentication for different connection methods, or because the password in your configuration doesn't match the actual PostgreSQL user password.

## Understanding PostgreSQL Authentication Methods

PostgreSQL uses different authentication methods depending on how you connect:

1. **Peer Authentication** (Unix Socket):
   - Used when connecting locally without specifying a host
   - Requires that your operating system username matches the PostgreSQL username
   - Example: `psql -U postgres`

2. **Password Authentication** (TCP/IP):
   - Used when connecting with an explicit host
   - Requires the correct password for the PostgreSQL user
   - Example: `psql -h localhost -U postgres`

## Solutions

### Solution 1: Reset PostgreSQL User Password

If the password in your configuration doesn't match the actual PostgreSQL user password, you can reset it:

```bash
# Connect to PostgreSQL as a superuser
sudo -u postgres psql

# Reset the password for the postgres user
ALTER USER postgres WITH PASSWORD 'postgres';

# Exit PostgreSQL
\q
```

Make sure the password you set matches what's in your `.env` file.

### Solution 2: Use Password Authentication

Always specify the host when connecting to PostgreSQL to use password authentication:

```bash
# Set the password as an environment variable
export PGPASSWORD=postgres

# Connect using password authentication
psql -h localhost -U postgres -d booksl_train
```

Or use the provided script:

```bash
./connect-postgres.sh
```

### Solution 3: Use Peer Authentication

If you want to use peer authentication, you need to:

1. Create a PostgreSQL user that matches your operating system username:

```sql
CREATE USER instance_01 WITH PASSWORD 'your_password';
GRANT ALL PRIVILEGES ON DATABASE booksl_train TO instance_01;
```

2. Connect without specifying a host:

```bash
psql -U instance_01 -d booksl_train
```

## PHP Application Connection

The PHP application is configured to use password authentication and is working correctly. The connection parameters are defined in:

- `.env` file (for environment variables)
- `php_app/includes/db_connection.php` (for the PHP connection)

## Testing Connection

You can test the PostgreSQL connection using the provided PHP scripts:

```bash
# Basic connection test
php php_app/includes/test_connection.php

# Comprehensive connection test
php php_app/includes/postgres_connection_test.php
```

These scripts will show detailed information about your connection parameters and test different connection methods.

## PostgreSQL Configuration

The PostgreSQL authentication methods are defined in the `pg_hba.conf` file:

```
/etc/postgresql/17/main/pg_hba.conf
```

This file controls how PostgreSQL authenticates users for different connection types:

```
local   all             postgres                                peer
local   all             all                                     trust
host    all             all             127.0.0.1/32            md5
host    all             all             ::1/128                 md5
```

The important lines are:
- `local all postgres peer` - Local connections to the postgres user use peer authentication
- `host all all 127.0.0.1/32 md5` - TCP/IP connections use password (md5) authentication

## Verifying Your Connection

You can verify your connection settings and authentication method with:

```bash
# Check if you can connect with password authentication
PGPASSWORD=postgres psql -h localhost -U postgres -c "SELECT 1 as connection_test;"

# Check if you can connect with peer authentication
sudo -u postgres psql -c "SELECT 1 as connection_test;"
```

## Common Issues and Solutions

1. **Password Mismatch**: The most common issue is that the password in your configuration doesn't match the actual PostgreSQL user password. Reset the password as shown above.

2. **Authentication Method Confusion**: Make sure you're using the right authentication method for your connection type:
   - For PHP applications, always use password authentication by specifying the host
   - For command-line tools, you can use either method depending on your needs

3. **PostgreSQL Not Running**: Ensure PostgreSQL is running with:
   ```bash
   sudo systemctl status postgresql
   ```

4. **Wrong Port**: Verify you're using the correct port (default is 5432)

5. **Database Doesn't Exist**: Make sure the database exists:
   ```bash
   sudo -u postgres psql -c "SELECT datname FROM pg_database;"
   ```
