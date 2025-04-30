import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Dashboard from './components/Dashboard';
import TrainSchedules from './components/TrainSchedules';
import TicketSales from './components/TicketSales';
import RemoteManagement from './components/RemoteManagement';
import TrainFleet from './components/TrainFleet';
import Layout from './components/Layout';
import './index.css';

// Import or create placeholder components for the additional pages
import Reports from './components/Reports';
import ErpModeling from './components/ErpModeling';
import Passengers from './components/Passengers';
import SystemLogs from './components/SystemLogs';

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={
          <Layout title="Dashboard - BookSL Train Management System">
            <Dashboard />
          </Layout>
        } />
        <Route path="/train-schedules" element={
          <Layout title="Train Schedules - BookSL Train Management System">
            <TrainSchedules />
          </Layout>
        } />
        <Route path="/ticket-sales" element={
          <Layout title="Ticket Sales - BookSL Train Management System">
            <TicketSales />
          </Layout>
        } />
        <Route path="/remote-management" element={
          <Layout title="Remote Management - BookSL Train Management System">
            <RemoteManagement />
          </Layout>
        } />
        <Route path="/train-fleet" element={
          <Layout title="Train Fleet - BookSL Train Management System">
            <TrainFleet />
          </Layout>
        } />
        <Route path="/reports" element={
          <Layout title="Reports - BookSL Train Management System">
            <Reports />
          </Layout>
        } />
        <Route path="/erp-modeling" element={
          <Layout title="ERP Modeling - BookSL Train Management System">
            <ErpModeling />
          </Layout>
        } />
        <Route path="/passengers" element={
          <Layout title="Passengers - BookSL Train Management System">
            <Passengers />
          </Layout>
        } />
        <Route path="/system-logs" element={
          <Layout title="System Logs - BookSL Train Management System">
            <SystemLogs />
          </Layout>
        } />
      </Routes>
    </Router>
  );
}

export default App;
