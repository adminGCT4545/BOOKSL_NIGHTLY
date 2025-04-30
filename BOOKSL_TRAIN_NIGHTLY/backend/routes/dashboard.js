import express from 'express';
import trainModel from '../models/trainModel.js';

const router = express.Router();

/**
 * @route GET /api/dashboard/test
 * @desc Test dashboard route
 * @access Public
 */
router.get('/test', (req, res) => {
  res.json({ message: 'Dashboard API is working' });
});

/**
 * @route GET /api/dashboard/trains
 * @desc Get all trains
 * @access Public
 */
router.get('/trains', async (req, res) => {
  try {
    const trains = await trainModel.getAllTrains();
    res.json(trains);
  } catch (err) {
    console.error('Error fetching trains:', err);
    res.status(500).json({ error: err.message });
  }
});

/**
 * @route GET /api/dashboard/journeys
 * @desc Get all train journeys with optional filtering
 * @access Public
 */
router.get('/journeys', async (req, res) => {
  try {
    const { year, trainId, limit } = req.query;
    const journeys = await trainModel.getJourneys(year, trainId, limit);
    res.json(journeys);
  } catch (err) {
    console.error('Error fetching journeys:', err);
    res.status(500).json({ error: err.message });
  }
});

/**
 * @route GET /api/dashboard/upcoming
 * @desc Get upcoming train departures
 * @access Public
 */
router.get('/upcoming', async (req, res) => {
  try {
    const count = req.query.count || 5;
    const departures = await trainModel.getUpcomingDepartures(count);
    res.json(departures);
  } catch (err) {
    console.error('Error fetching upcoming departures:', err);
    res.status(500).json({ error: err.message });
  }
});

/**
 * @route GET /api/dashboard/stats
 * @desc Get dashboard statistics
 * @access Public
 */
router.get('/stats', async (req, res) => {
  try {
    const stats = await trainModel.getDashboardStats();
    res.json(stats);
  } catch (err) {
    console.error('Error fetching dashboard stats:', err);
    res.status(500).json({ error: err.message });
  }
});

/**
 * @route GET /api/dashboard/years
 * @desc Get available years in the data
 * @access Public
 */
router.get('/years', async (req, res) => {
  try {
    const years = await trainModel.getAvailableYears();
    res.json(years);
  } catch (err) {
    console.error('Error fetching available years:', err);
    res.status(500).json({ error: err.message });
  }
});

export default router;
