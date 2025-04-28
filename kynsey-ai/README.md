# BookSL Train Management System

This project integrates the BookSL Train Management System into the KYNSEY AI application. It provides a comprehensive dashboard and management interface for train operations, schedules, ticket sales, and system monitoring.

## Features

- **Dashboard**: Overview of train operations, revenue, occupancy, and performance metrics
- **Train Schedules**: Detailed view of train schedules, routes, and status
- **Ticket Sales**: Analysis of ticket sales and revenue by train, class, and route
- **Remote Management**: Remote monitoring and control of train operations
- **Train Fleet**: Management and monitoring of the train fleet
- **Reports**: Comprehensive reporting on train operations
- **Modeling**: ERP modeling for train operations
- **Passengers**: Passenger information and statistics
- **System Logs**: System activity logs for monitoring and troubleshooting

## Prerequisites

- Node.js (v16 or higher)
- PostgreSQL database with BookSL Train data
- Ollama for AI functionality (optional)

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/your-username/booksl-train-management.git
   cd booksl-train-management
   ```

2. Install dependencies:
   ```
   cd kynsey-ai/backend
   npm install
   ```

3. Set up environment variables:
   Create a `.env` file in the `kynsey-ai/backend` directory with the following variables:
   ```
   PORT=3001
   PGHOST=localhost
   PGUSER=postgres
   PGPASSWORD=postgres
   PGDATABASE=booksl_train
   PGPORT=5432
   OLLAMA_HOST=http://localhost:11434
   ADMIN_SECRET=your-admin-secret
   ```

## Running the Application

1. Start the server:
   ```
   cd kynsey-ai/backend
   npm start
   ```

2. Open your browser and navigate to:
   ```
   http://localhost:3001
   ```

## Development

For development with hot reloading:
```
cd kynsey-ai/backend
npm run dev
```

## Database Setup

The application requires a PostgreSQL database with the BookSL Train schema. The database should include the following tables:

- trains
- train_schedules
- train_journeys
- system_logs

Refer to the database schema in the `setup-database.sql` file for details.

## API Endpoints

The application provides the following API endpoints:

- `/api/dashboard`: Dashboard data
- `/api/train-schedules`: Train schedules data
- `/api/ticket-sales`: Ticket sales data
- `/api/remote-management`: Remote management data
- `/api/train-fleet`: Train fleet data
- `/api/reports`: Reports data
- `/api/modeling`: Modeling data
- `/api/passengers`: Passengers data
- `/api/system-logs`: System logs data
- `/api/db-status`: Database connection status

## AI Integration

The application integrates with Ollama for AI functionality. The AI can be used for:

- Natural language queries about train operations
- Predictive analytics for train performance
- Anomaly detection in train operations

## License

MIT
