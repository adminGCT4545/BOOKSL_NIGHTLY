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

## Installation

1. Clone the repository:

```bash
git clone <repository-url>
cd BookSL-Train-Dashboard
```

2. Install dependencies:

```bash
npm install
# or
yarn
```

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
│   ├── App.tsx           # Main application component
│   ├── main.tsx          # Application entry point
│   └── index.css         # Global styles
├── index.html            # HTML template
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
- [Papa Parse](https://www.papaparse.com/) - CSV parsing library

## Features

- **Interactive Dashboard**: Filter and analyze data with interactive controls
- **Real-time Monitoring**: Track train schedules and delays
- **Revenue Analysis**: Visualize ticket sales and revenue by train and class
- **Occupancy Tracking**: Monitor train occupancy rates
- **Performance Metrics**: Track key performance indicators

## Development

### Available Scripts

- `npm run dev` - Start the development server
- `npm run build` - Build for production
- `npm run lint` - Run ESLint to check code quality
- `npm run preview` - Preview the production build locally

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
