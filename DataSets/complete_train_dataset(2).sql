(103, 'Kandy', 'Colombo', '2024-07-28', 'Third', 120, 106, true, 17098.74),
(105, 'Kandy', 'Colombo', '2024-05-10', 'Third', 120, 87, true, 12804.29),
(106, 'Kandy', 'Colombo', '2024-07-02', 'Second', 80, 60, false, 16429.20),
(106, 'Ella', 'Kandy', '2025-02-09', 'First', 50, 48, true, 27461.20),
(107, 'Ella', 'Colombo', '2024-09-17', 'First', 50, 34, false, 18412.07),
(107, 'Colombo', 'Kandy', '2024-07-05', 'Second', 80, 55, true, 16481.99),
(102, 'Kandy', 'Ella', '2025-05-18', 'Third', 120, 93, false, 14053.95),
(101, 'Ella', 'Colombo', '2024-04-03', 'First', 50, 38, false, 20984.19),
(108, 'Colombo', 'Ella', '2025-01-15', 'Second', 80, 71, true, 20553.20),
(103, 'Kandy', 'Colombo', '2024-12-23', 'Third', 120, 106, false, 16193.92),
(104, 'Ella', 'Kandy', '2024-03-17', 'First', 50, 35, true, 19686.27),
(101, 'Colombo', 'Ella', '2025-02-26', 'Second', 80, 62, false, 18633.03),
(107, 'Kandy', 'Colombo', '2024-10-27', 'Third', 120, 80, true, 12060.87),
(105, 'Ella', 'Kandy', '2025-05-02', 'First', 50, 47, false, 26622.15),
(102, 'Colombo', 'Kandy', '2024-08-09', 'Second', 80, 63, true, 18633.03),
(105, 'Colombo', 'Ella', '2024-04-29', 'Second', 80, 50, false, 16167.33),
(107, 'Ella', 'Colombo', '2024-10-04', 'First', 50, 33, false, 21421.05),
(108, 'Colombo', 'Kandy', '2024-03-17', 'Second', 80, 62, true, 18908.97),
(105, 'Kandy', 'Ella', '2024-02-16', 'Third', 120, 111, false, 15913.01),
(105, 'Ella', 'Colombo', '2024-12-29', 'Second', 80, 64, false, 18732.60),
(101, 'Kandy', 'Colombo', '2025-04-04', 'First', 50, 45, true, 26042.11),
(104, 'Ella', 'Kandy', '2025-03-28', 'Third', 120, 102, false, 15914.85),
(107, 'Colombo', 'Ella', '2024-05-05', 'Second', 80, 57, true, 16252.34),
(102, 'Kandy', 'Colombo', '2024-06-28', 'First', 50, 36, false, 20736.72),
(103, 'Ella', 'Kandy', '2025-05-01', 'Third', 120, 95, true, 13578.84),
(106, 'Colombo', 'Ella', '2025-01-22', 'Second', 80, 60, false, 17673.00),
(104, 'Kandy', 'Colombo', '2024-09-02', 'First', 50, 41, true, 23535.42),
(108, 'Ella', 'Kandy', '2024-08-15', 'Third', 120, 80, false, 12591.24),
(101, 'Colombo', 'Kandy', '2025-02-07', 'Second', 80, 54, true, 15780.18),
(105, 'Kandy', 'Ella', '2024-11-09', 'First', 50, 39, false, 22596.30),
(103, 'Colombo', 'Ella', '2024-07-20', 'Third', 120, 78, true, 11248.86),
(106, 'Kandy', 'Colombo', '2025-04-25', 'Second', 80, 55, false, 15842.40),
(102, 'Ella', 'Kandy', '2025-01-18', 'First', 50, 40, true, 23731.20),
(108, 'Kandy', 'Colombo', '2024-09-23', 'Third', 120, 85, true, 11616.89);

-- 3. KPI Queries for ERP Dashboard

-- Overall Occupancy Percentage
SELECT 
    ROUND((SUM(ReservedSeats) * 100.0 / SUM(TotalSeats)), 2) AS overall_occupancy_percentage
FROM 
    TrainJourneyStats;

-- Overall Percentage of Delays
SELECT 
    ROUND((SUM(CASE WHEN IsDelayed THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) AS overall_delay_percentage
FROM 
    TrainJourneyStats;

-- Total Revenue
SELECT 
    SUM(Revenue) AS total_revenue
FROM 
    TrainJourneyStats;

-- KPIs Grouped by TrainID
SELECT 
    TrainID,
    ROUND((SUM(ReservedSeats) * 100.0 / SUM(TotalSeats)), 2) AS train_occupancy_percentage,
    ROUND((SUM(CASE WHEN IsDelayed THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) AS train_delay_percentage,
    SUM(Revenue) AS train_total_revenue
FROM 
    TrainJourneyStats
GROUP BY 
    TrainID
ORDER BY 
    TrainID;

-- KPIs Grouped by Class
SELECT 
    Class,
    ROUND((SUM(ReservedSeats) * 100.0 / SUM(TotalSeats)), 2) AS class_occupancy_percentage,
    ROUND((SUM(CASE WHEN IsDelayed THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) AS class_delay_percentage,
    SUM(Revenue) AS class_total_revenue
FROM 
    TrainJourneyStats
GROUP BY 
    Class
ORDER BY 
    Class;

-- KPIs Grouped by Route (Combination of DepartureCity and ArrivalCity)
SELECT 
    DepartureCity || ' to ' || ArrivalCity AS route,
    ROUND((SUM(ReservedSeats) * 100.0 / SUM(TotalSeats)), 2) AS route_occupancy_percentage,
    ROUND((SUM(CASE WHEN IsDelayed THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) AS route_delay_percentage,
    SUM(Revenue) AS route_total_revenue
FROM 
    TrainJourneyStats
GROUP BY 
    DepartureCity, ArrivalCity
ORDER BY 
    route;

-- Monthly KPIs
SELECT 
    TO_CHAR(JourneyDate, 'YYYY-MM') AS month,
    ROUND((SUM(ReservedSeats) * 100.0 / SUM(TotalSeats)), 2) AS monthly_occupancy_percentage,
    ROUND((SUM(CASE WHEN IsDelayed THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) AS monthly_delay_percentage,
    SUM(Revenue) AS monthly_total_revenue
FROM 
    TrainJourneyStats
GROUP BY 
    TO_CHAR(JourneyDate, 'YYYY-MM')
ORDER BY 
    month;

-- New KPI Queries for 2024 vs 2025 Comparison

-- Year-wise KPIs
SELECT 
    EXTRACT(YEAR FROM JourneyDate) AS year,
    ROUND((SUM(ReservedSeats) * 100.0 / SUM(TotalSeats)), 2) AS yearly_occupancy_percentage,
    ROUND((SUM(CASE WHEN IsDelayed THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) AS yearly_delay_percentage,
    SUM(Revenue) AS yearly_total_revenue,
    COUNT(*) AS journey_count
FROM 
    TrainJourneyStats
GROUP BY 
    EXTRACT(YEAR FROM JourneyDate)
ORDER BY 
    year;

-- Class-wise KPIs by Year
SELECT 
    EXTRACT(YEAR FROM JourneyDate) AS year,
    Class,
    ROUND((SUM(ReservedSeats) * 100.0 / SUM(TotalSeats)), 2) AS class_occupancy_percentage,
    ROUND((SUM(CASE WHEN IsDelayed THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) AS class_delay_percentage,
    SUM(Revenue) AS class_total_revenue,
    COUNT(*) AS journey_count
FROM 
    TrainJourneyStats
GROUP BY 
    EXTRACT(YEAR FROM JourneyDate), Class
ORDER BY 
    year, Class;

-- Train-wise KPIs by Year
SELECT 
    EXTRACT(YEAR FROM JourneyDate) AS year,
    TrainID,
    ROUND((SUM(ReservedSeats) * 100.0 / SUM(TotalSeats)), 2) AS train_occupancy_percentage,
    ROUND((SUM(CASE WHEN IsDelayed THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) AS train_delay_percentage,
    SUM(Revenue) AS train_total_revenue,
    COUNT(*) AS journey_count
FROM 
    TrainJourneyStats
GROUP BY 
    EXTRACT(YEAR FROM JourneyDate), TrainID
ORDER BY 
    year, TrainID;

-- Route-wise KPIs by Year
SELECT 
    EXTRACT(YEAR FROM JourneyDate) AS year,
    DepartureCity || ' to ' || ArrivalCity AS route,
    ROUND((SUM(ReservedSeats) * 100.0 / SUM(TotalSeats)), 2) AS route_occupancy_percentage,
    ROUND((SUM(CASE WHEN IsDelayed THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) AS route_delay_percentage,
    SUM(Revenue) AS route_total_revenue,
    COUNT(*) AS journey_count
FROM 
    TrainJourneyStats
GROUP BY 
    EXTRACT(YEAR FROM JourneyDate), DepartureCity, ArrivalCity
ORDER BY 
    year, route;-- 1. CREATE TABLE Statement for TrainJourneyStats
CREATE TABLE TrainJourneyStats (
    JourneyID SERIAL PRIMARY KEY,  -- PostgreSQL uses SERIAL instead of INTEGER AUTOINCREMENT
    TrainID INTEGER NOT NULL,
    DepartureCity TEXT NOT NULL CHECK(DepartureCity IN ('Colombo', 'Kandy', 'Ella')),
    ArrivalCity TEXT NOT NULL CHECK(ArrivalCity IN ('Colombo', 'Kandy', 'Ella')),
    JourneyDate DATE NOT NULL,
    Class TEXT NOT NULL CHECK(Class IN ('First', 'Second', 'Third')),
    TotalSeats INTEGER NOT NULL,
    ReservedSeats INTEGER NOT NULL,
    IsDelayed BOOLEAN NOT NULL,  -- PostgreSQL uses BOOLEAN type (true/false) instead of INTEGER
    Revenue REAL NOT NULL,
    CHECK(DepartureCity != ArrivalCity),
    CHECK(ReservedSeats <= TotalSeats)  -- Added constraint to ensure reserved seats don't exceed total
);

-- 2. INSERT Statements with Sample Data
-- TrainID: 101, 102, 103, 104, 105, 106, 107, 108
-- Cities: Colombo, Kandy, Ella
-- Classes: First, Second, Third

INSERT INTO TrainJourneyStats (TrainID, DepartureCity, ArrivalCity, JourneyDate, Class, TotalSeats, ReservedSeats, IsDelayed, Revenue) VALUES
-- Original January 2025 data
(101, 'Colombo', 'Kandy', '2025-01-05', 'First', 50, 45, true, 22500.00),
(101, 'Kandy', 'Colombo', '2025-01-05', 'First', 50, 48, false, 24000.00),
(102, 'Colombo', 'Ella', '2025-01-05', 'Second', 80, 65, false, 19500.00),
(102, 'Ella', 'Colombo', '2025-01-05', 'Second', 80, 72, true, 21600.00),
(103, 'Kandy', 'Ella', '2025-01-06', 'Third', 120, 95, false, 14250.00),
(103, 'Ella', 'Kandy', '2025-01-06', 'Third', 120, 110, false, 16500.00),
(104, 'Colombo', 'Kandy', '2025-01-06', 'Second', 80, 78, true, 23400.00),
(104, 'Kandy', 'Colombo', '2025-01-06', 'Second', 80, 76, false, 22800.00),
(101, 'Colombo', 'Ella', '2025-01-07', 'First', 50, 42, false, 25200.00),
(101, 'Ella', 'Colombo', '2025-01-07', 'First', 50, 47, true, 28200.00),
(102, 'Kandy', 'Ella', '2025-01-07', 'Third', 120, 85, false, 12750.00),
(102, 'Ella', 'Kandy', '2025-01-07', 'Third', 120, 92, true, 13800.00),

-- February 2025 data
(103, 'Colombo', 'Kandy', '2025-02-10', 'First', 50, 49, false, 24500.00),
(103, 'Kandy', 'Colombo', '2025-02-10', 'First', 50, 50, false, 25000.00),
(104, 'Colombo', 'Ella', '2025-02-10', 'Second', 80, 64, true, 19200.00),
(104, 'Ella', 'Colombo', '2025-02-10', 'Second', 80, 70, false, 21000.00),
(101, 'Kandy', 'Ella', '2025-02-11', 'Third', 120, 98, false, 14700.00),
(101, 'Ella', 'Kandy', '2025-02-11', 'Third', 120, 105, false, 15750.00),
(102, 'Colombo', 'Kandy', '2025-02-11', 'Second', 80, 75, true, 22500.00),
(102, 'Kandy', 'Colombo', '2025-02-11', 'Second', 80, 78, false, 23400.00),
(103, 'Colombo', 'Ella', '2025-02-12', 'First', 50, 44, false, 26400.00),
(103, 'Ella', 'Colombo', '2025-02-12', 'First', 50, 46, true, 27600.00),
(104, 'Kandy', 'Ella', '2025-02-12', 'Third', 120, 88, false, 13200.00),
(104, 'Ella', 'Kandy', '2025-02-12', 'Third', 120, 91, true, 13650.00),

-- March 2025 data
(101, 'Colombo', 'Kandy', '2025-03-15', 'First', 50, 48, false, 24000.00),
(101, 'Kandy', 'Colombo', '2025-03-15', 'First', 50, 47, false, 23500.00),
(102, 'Colombo', 'Ella', '2025-03-15', 'Second', 80, 76, true, 22800.00),
(102, 'Ella', 'Colombo', '2025-03-15', 'Second', 80, 74, false, 22200.00),
(103, 'Kandy', 'Ella', '2025-03-16', 'Third', 120, 112, true, 16800.00),
(103, 'Ella', 'Kandy', '2025-03-16', 'Third', 120, 108, false, 16200.00),
(104, 'Colombo', 'Kandy', '2025-03-16', 'Second', 80, 77, false, 23100.00),
(104, 'Kandy', 'Colombo', '2025-03-16', 'Second', 80, 75, true, 22500.00),
(101, 'Colombo', 'Ella', '2025-03-17', 'First', 50, 48, false, 28800.00),
(101, 'Ella', 'Colombo', '2025-03-17', 'First', 50, 46, false, 27600.00),
(102, 'Kandy', 'Ella', '2025-03-17', 'Third', 120, 94, true, 14100.00),
(102, 'Ella', 'Kandy', '2025-03-17', 'Third', 120, 90, false, 13500.00),

-- April 2025 data
(103, 'Colombo', 'Kandy', '2025-04-20', 'First', 50, 43, false, 21500.00),
(103, 'Kandy', 'Colombo', '2025-04-20', 'First', 50, 45, true, 22500.00),
(104, 'Colombo', 'Ella', '2025-04-20', 'Second', 80, 68, false, 20400.00),
(104, 'Ella', 'Colombo', '2025-04-20', 'Second', 80, 72, false, 21600.00),
(101, 'Kandy', 'Ella', '2025-04-21', 'Third', 120, 100, true, 15000.00),
(101, 'Ella', 'Kandy', '2025-04-21', 'Third', 120, 106, false, 15900.00),
(102, 'Colombo', 'Kandy', '2025-04-21', 'Second', 80, 70, false, 21000.00),
(102, 'Kandy', 'Colombo', '2025-04-21', 'Second', 80, 73, true, 21900.00),
(103, 'Colombo', 'Ella', '2025-04-22', 'First', 50, 40, false, 24000.00),
(103, 'Ella', 'Colombo', '2025-04-22', 'First', 50, 42, true, 25200.00),
(104, 'Kandy', 'Ella', '2025-04-22', 'Third', 120, 96, false, 14400.00),
(104, 'Ella', 'Kandy', '2025-04-22', 'Third', 120, 98, false, 14700.00),

-- Additional April 2025 data from paste.txt
(101, 'Kandy', 'Ella', '2025-04-23', 'Third', 120, 102, false, 15300.00),
(101, 'Ella', 'Kandy', '2025-04-23', 'Third', 120, 108, true, 16200.00),
(102, 'Colombo', 'Kandy', '2025-04-23', 'Second', 80, 74, false, 22200.00),
(102, 'Kandy', 'Colombo', '2025-04-23', 'Second', 80, 76, true, 22800.00),
(103, 'Colombo', 'Ella', '2025-04-23', 'First', 50, 41, false, 24600.00),
(103, 'Ella', 'Colombo', '2025-04-23', 'First', 50, 43, true, 25800.00),
(104, 'Kandy', 'Ella', '2025-04-23', 'Third', 120, 94, false, 14100.00),
(104, 'Ella', 'Kandy', '2025-04-23', 'Third', 120, 96, true, 14400.00),
(101, 'Kandy', 'Ella', '2025-04-24', 'Third', 120, 104, false, 15600.00),
(101, 'Ella', 'Kandy', '2025-04-24', 'Third', 120, 106, true, 15900.00),
(102, 'Colombo', 'Kandy', '2025-04-24', 'Second', 80, 71, false, 21300.00),
(102, 'Kandy', 'Colombo', '2025-04-24', 'Second', 80, 74, true, 22200.00),
(103, 'Colombo', 'Ella', '2025-04-24', 'First', 50, 40, true, 24600.00),
(103, 'Ella', 'Colombo', '2025-04-24', 'First', 50, 42, false, 25200.00),
(104, 'Kandy', 'Ella', '2025-04-24', 'Third', 120, 92, false, 13800.00),
(104, 'Ella', 'Kandy', '2025-04-24', 'Third', 120, 94, true, 14100.00),
(101, 'Kandy', 'Ella', '2025-04-25', 'Third', 120, 100, true, 15000.00),
(101, 'Ella', 'Kandy', '2025-04-25', 'Third', 120, 104, false, 15600.00),
(102, 'Colombo', 'Kandy', '2025-04-25', 'Second', 80, 70, true, 21900.00),
(102, 'Kandy', 'Colombo', '2025-04-25', 'Second', 80, 73, false, 21900.00),
(103, 'Colombo', 'Ella', '2025-04-25', 'First', 50, 41, false, 24300.00),
(103, 'Ella', 'Colombo', '2025-04-25', 'First', 50, 43, true, 25500.00),
(104, 'Kandy', 'Ella', '2025-04-25', 'Third', 120, 90, false, 13500.00),
(104, 'Ella', 'Kandy', '2025-04-25', 'Third', 120, 92, true, 13800.00),
(101, 'Kandy', 'Ella', '2025-04-26', 'Third', 120, 102, false, 15300.00),
(101, 'Ella', 'Kandy', '2025-04-26', 'Third', 120, 106, true, 16200.00),
(102, 'Colombo', 'Kandy', '2025-04-26', 'Second', 80, 71, false, 21300.00),
(102, 'Kandy', 'Colombo', '2025-04-26', 'Second', 80, 74, true, 22200.00),
(103, 'Colombo', 'Ella', '2025-04-26', 'First', 50, 40, true, 24600.00),
(103, 'Ella', 'Colombo', '2025-04-26', 'First', 50, 42, false, 25200.00),
(104, 'Kandy', 'Ella', '2025-04-26', 'Third', 120, 92, false, 13800.00),
(104, 'Ella', 'Kandy', '2025-04-26', 'Third', 120, 94, true, 14100.00),
(101, 'Kandy', 'Ella', '2025-04-27', 'Third', 120, 100, true, 15000.00),
(101, 'Ella', 'Kandy', '2025-04-27', 'Third', 120, 104, false, 15600.00),
(102, 'Colombo', 'Kandy', '2025-04-27', 'Second', 80, 70, true, 21900.00),
(102, 'Kandy', 'Colombo', '2025-04-27', 'Second', 80, 73, false, 21900.00),
(103, 'Colombo', 'Ella', '2025-04-27', 'First', 50, 41, false, 24300.00),
(103, 'Ella', 'Colombo', '2025-04-27', 'First', 50, 43, true, 25500.00),
(104, 'Kandy', 'Ella', '2025-04-27', 'Third', 120, 90, false, 13500.00),
(104, 'Ella', 'Kandy', '2025-04-27', 'Third', 120, 92, true, 13800.00),
(101, 'Kandy', 'Ella', '2025-04-28', 'Third', 120, 102, false, 15300.00),
(101, 'Ella', 'Kandy', '2025-04-28', 'Third', 120, 106, true, 16200.00),
(102, 'Colombo', 'Kandy', '2025-04-28', 'Second', 80, 71, false, 21300.00),
(102, 'Kandy', 'Colombo', '2025-04-28', 'Second', 80, 74, true, 22200.00),
(103, 'Colombo', 'Ella', '2025-04-28', 'First', 50, 40, true, 24600.00),
(103, 'Ella', 'Colombo', '2025-04-28', 'First', 50, 42, false, 25200.00),
(104, 'Kandy', 'Ella', '2025-04-28', 'Third', 120, 92, false, 13800.00),
(104, 'Ella', 'Kandy', '2025-04-28', 'Third', 120, 94, true, 14100.00),
(101, 'Kandy', 'Ella', '2025-04-29', 'Third', 120, 100, true, 15000.00),
(101, 'Ella', 'Kandy', '2025-04-29', 'Third', 120, 104, false, 15600.00),
(102, 'Colombo', 'Kandy', '2025-04-29', 'Second', 80, 70, true, 21900.00),
(102, 'Kandy', 'Colombo', '2025-04-29', 'Second', 80, 73, false, 21900.00),
(103, 'Colombo', 'Ella', '2025-04-29', 'First', 50, 41, false, 24300.00),
(103, 'Ella', 'Colombo', '2025-04-29', 'First', 50, 43, true, 25500.00),
(104, 'Kandy', 'Ella', '2025-04-29', 'Third', 120, 90, false, 13500.00),
(104, 'Ella', 'Kandy', '2025-04-29', 'Third', 120, 92, true, 13800.00),
(101, 'Kandy', 'Ella', '2025-04-30', 'Third', 120, 102, false, 15300.00),
(101, 'Ella', 'Kandy', '2025-04-30', 'Third', 120, 106, true, 16200.00),
(102, 'Colombo', 'Kandy', '2025-04-30', 'Second', 80, 71, false, 21300.00),
(102, 'Kandy', 'Colombo', '2025-04-30', 'Second', 80, 74, true, 22200.00),
(103, 'Colombo', 'Ella', '2025-04-30', 'First', 50, 40, true, 24600.00),
(103, 'Ella', 'Colombo', '2025-04-30', 'First', 50, 42, false, 25200.00),
(104, 'Kandy', 'Ella', '2025-04-30', 'Third', 120, 92, false, 13800.00),
(104, 'Ella', 'Kandy', '2025-04-30', 'Third', 120, 94, true, 14100.00),

-- May 2025 data (original)
(101, 'Colombo', 'Kandy', '2025-05-01', 'First', 50, 47, true, 23500.00),
(102, 'Kandy', 'Colombo', '2025-05-01', 'First', 50, 49, false, 24500.00),
(103, 'Colombo', 'Ella', '2025-05-01', 'Second', 80, 67, false, 20100.00),
(104, 'Ella', 'Colombo', '2025-05-01', 'Second', 80, 69, true, 20700.00),
(101, 'Kandy', 'Ella', '2025-05-02', 'Third', 120, 103, false, 15450.00),
(102, 'Ella', 'Kandy', '2025-05-02', 'Third', 120, 107, false, 16050.00),

-- Additional May 2025 data from paste.txt
(101, 'Kandy', 'Ella', '2025-05-01', 'Third', 120, 100, true, 15000.00),
(101, 'Ella', 'Kandy', '2025-05-01', 'Third', 120, 104, false, 15600.00),
(102, 'Colombo', 'Kandy', '2025-05-01', 'Second', 80, 70, true, 21900.00),
(102, 'Kandy', 'Colombo', '2025-05-01', 'Second', 80, 73, false, 21900.00),
(103, 'Colombo', 'Ella', '2025-05-01', 'First', 50, 41, false, 24300.00),
(103, 'Ella', 'Colombo', '2025-05-01', 'First', 50, 43, true, 25500.00),
(104, 'Kandy', 'Ella', '2025-05-01', 'Third', 120, 90, false, 13500.00),
(104, 'Ella', 'Kandy', '2025-05-01', 'Third', 120, 92, true, 13800.00),
(101, 'Kandy', 'Ella', '2025-05-02', 'Third', 120, 102, false, 15300.00),
(101, 'Ella', 'Kandy', '2025-05-02', 'Third', 120, 106, true, 16200.00),
(102, 'Colombo', 'Kandy', '2025-05-02', 'Second', 80, 71, false, 21300.00),
(102, 'Kandy', 'Colombo', '2025-05-02', 'Second', 80, 74, true, 22200.00),
(103, 'Colombo', 'Ella', '2025-05-02', 'First', 50, 40, true, 24600.00),
(103, 'Ella', 'Colombo', '2025-05-02', 'First', 50, 42, false, 25200.00),
(104, 'Kandy', 'Ella', '2025-05-02', 'Third', 120, 92, false, 13800.00),
(104, 'Ella', 'Kandy', '2025-05-02', 'Third', 120, 94, true, 14100.00),
(101, 'Kandy', 'Ella', '2025-05-03', 'Third', 120, 100, true, 15000.00),
(101, 'Ella', 'Kandy', '2025-05-03', 'Third', 120, 104, false, 15600.00),
(102, 'Colombo', 'Kandy', '2025-05-03', 'Second', 80, 70, true, 21900.00),
(102, 'Kandy', 'Colombo', '2025-05-03', 'Second', 80, 73, false, 21900.00),

-- 3000 Additional randomly generated journeys (includes 2024 data)
(102, 'Ella', 'Colombo', '2025-02-16', 'Second', 80, 54, false, 16303.62),
(105, 'Ella', 'Colombo', '2024-01-11', 'Third', 120, 113, false, 18211.05),
(107, 'Colombo', 'Ella', '2025-05-26', 'First', 50, 40, false, 24758.76),
(101, 'Kandy', 'Colombo', '2024-07-22', 'Third', 120, 74, false, 10718.51),
(108, 'Ella', 'Colombo', '2025-01-14', 'First', 50, 49, false, 28619.61),
(105, 'Colombo', 'Ella', '2024-06-15', 'Third', 120, 81, false, 12478.04),
(102, 'Kandy', 'Colombo', '2024-06-15', 'Third', 120, 72, false, 11056.98),
(103, 'Ella', 'Colombo', '2024-06-15', 'First', 50, 37, true, 24074.57),
(103, 'Ella', 'Kandy', '2024-06-15', 'Second', 80, 54, false, 14909.75),
(103, 'Kandy', 'Colombo', '2024-06-15', 'Third', 120, 98, true, 13754.50),
(108, 'Kandy', 'Colombo', '2024-06-15', 'Second', 80, 60, true, 17081.62),
(102, 'Ella', 'Colombo', '2024-06-15', 'Third', 120, 106, false, 14861.04),
(103, 'Ella', 'Kandy', '2024-06-15', 'Second', 80, 70, false, 19544.18),
(105, 'Ella', 'Kandy', '2024-06-15', 'First', 50, 48, true, 30462.16),
(103, 'Kandy', 'Colombo', '2024-06-15', 'First', 50, 47, false, 26636.58),
(106, 'Kandy', 'Ella', '2024-03-22', 'Third', 120, 102, true, 15956.40),
(104, 'Ella', 'Colombo', '2024-07-14', 'Second', 80, 65, false, 20345.85),
(107, 'Colombo', 'Kandy', '2024-10-05', 'First', 50, 43, true, 27144.90),
(108, 'Kandy', 'Ella', '2024-11-18', 'Third', 120, 96, true, 15342.72),
(105, 'Ella', 'Colombo', '2024-05-29', 'Second', 80, 75, false, 21956.25),
(107, 'Colombo', 'Ella', '2025-03-12', 'First', 50, 47, false, 28752.06),
(104, 'Ella', 'Kandy', '2025-01-23', 'Third', 120, 108, true, 17562.60),
(106, 'Kandy', 'Colombo', '2025-02-09', 'Second', 80, 62, false, 18937.50),
(105, 'Colombo', 'Ella', '2024-12-24', 'First', 50, 48, true, 29760.96),
(108, 'Kandy', 'Colombo', '2025-04-07', 'Third', 120, 97, false, 15941.25),
(106, 'Colombo', 'Kandy', '2024-08-31', 'First', 50, 42, true, 25243.80),
(107, 'Ella', 'Colombo', '2024-09-16', 'Second', 80, 68, false, 21046.20),
(108, 'Kandy', 'Ella', '2024-04-11', 'Third', 120, 101, true, 15452.85),
(105, 'Colombo', 'Kandy', '2025-03-28', 'Second', 80, 73, false, 21827.85),
(104, 'Ella', 'Colombo', '2025-05-09', 'First', 50, 44, true, 27676.56),
(106, 'Kandy', 'Ella', '2024-06-27', 'Third', 120, 105, false, 16854.75),
(107, 'Colombo', 'Kandy', '2024-07-08', 'Second', 80, 67, true, 19895.55),
(108, 'Ella', 'Colombo', '2025-02-16', 'First', 50, 46, false, 28354.92),
(105, 'Kandy', 'Ella', '2025-01-30', 'Third', 120, 112, true, 16968.00),
(104, 'Colombo', 'Kandy', '2024-11-12', 'Second', 80, 72, false, 21978.00),
(107, 'Kandy', 'Colombo', '2024-08-18', 'First', 50, 41, true, 25872.90),
(108, 'Colombo', 'Ella', '2025-04-25', 'Third', 120, 93, false, 14207.85),
(106, 'Ella', 'Kandy', '2024-12-05', 'Second', 80, 64, true, 19526.40),
(105, 'Kandy', 'Colombo', '2025-02-23', 'First', 50, 45, false, 26619.00),
(107, 'Ella', 'Colombo', '2024-10-29', 'Third', 120, 99, true, 15064.65),
(104, 'Colombo', 'Ella', '2025-01-11', 'Second', 80, 71, false, 21051.45),
(106, 'Kandy', 'Colombo', '2024-05-13', 'First', 50, 43, true, 25623.90),
(105, 'Colombo', 'Kandy', '2024-12-31', 'Third', 120, 110, false, 16912.50),
(108, 'Ella', 'Kandy', '2025-03-19', 'Second', 80, 66, true, 20245.20),
(107, 'Colombo', 'Ella', '2024-09-02', 'First', 50, 39, false, 23146.80),
(104, 'Kandy', 'Colombo', '2025-05-18', 'Third', 120, 95, true, 14392.50),
(106, 'Ella', 'Colombo', '2024-07-29', 'Second', 80, 69, false, 20651.25),
(105, 'Kandy', 'Ella', '2024-11-03', 'First', 50, 44, true, 26279.76),
(108, 'Colombo', 'Kandy', '2025-02-04', 'Third', 120, 102, false, 15354.90),
(107, 'Ella', 'Colombo', '2024-08-11', 'Second', 80, 65, true, 19526.25),
(106, 'Kandy', 'Ella', '2025-03-05', 'First', 50, 40, false, 23040.00),
(104, 'Colombo', 'Kandy', '2024-12-17', 'Third', 120, 94, true, 15134.10),
(105, 'Ella', 'Colombo', '2025-01-19', 'Second', 80, 68, false, 19618.80),
(107, 'Kandy', 'Ella', '2024-06-09', 'First', 50, 42, true, 25243.80),
(108, 'Colombo', 'Kandy', '2025-04-13', 'Third', 120, 106, false, 16334.70),
(106, 'Ella', 'Colombo', '2024-11-24', 'Second', 80, 63, true, 18512.85),
(104, 'Kandy', 'Ella', '2025-01-02', 'First', 50, 41, false, 23370.60),
(105, 'Colombo', 'Kandy', '2024-07-16', 'Third', 120, 97, true, 14819.55),
(108, 'Ella', 'Colombo', '2025-02-28', 'Second', 80, 70, false, 20462.50),
(107, 'Kandy', 'Ella', '2024-12-03', 'First', 50, 45, true, 25623.00),
(106, 'Colombo', 'Kandy', '2025-03-24', 'Third', 120, 103, false, 15605.25),
(104, 'Ella', 'Colombo', '2024-09-20', 'Second', 80, 64, true, 19526.40),
(105, 'Kandy', 'Ella', '2025-05-16', 'First', 50, 42, false, 24178.80),
(108, 'Colombo', 'Kandy', '2024-10-08', 'Third', 120, 98, true, 14935.80),
(107, 'Ella', 'Colombo', '2025-01-27', 'Second', 80, 69, false, 20012.25),
(106, 'Kandy', 'Ella', '2024-08-15', 'First', 50, 43, true, 24921.90),
(104, 'Colombo', 'Ella', '2025-04-02', 'Third', 120, 100, false, 15150.00),
(105, 'Kandy', 'Colombo', '2024-11-10', 'Second', 80, 66, true, 19856.70),
(108, 'Ella', 'Kandy', '2025-03-31', 'First', 50, 44, false, 25711.76),
(107, 'Colombo', 'Ella', '2024-06-22', 'Third', 120, 95, true, 14535.75),
(106, 'Kandy', 'Colombo', '2025-02-18', 'Second', 80, 67, false, 19528.05),
(104, 'Ella', 'Kandy', '2024-10-14', 'First', 50, 41, true, 23988.60),
(105, 'Colombo', 'Ella', '2025-01-05', 'Third', 120, 99, false, 15035.25),
(108, 'Kandy', 'Colombo', '2024-09-27', 'Second', 80, 65, true, 19254.75),
(107, 'Ella', 'Kandy', '2025-04-19', 'First', 50, 45, false, 27090.00),
(106, 'Colombo', 'Kandy', '2024-07-02', 'Third', 120, 101, true, 15756.00),
(104, 'Ella', 'Colombo', '2025-03-09', 'Second', 80, 68, false, 20126.40),
(105, 'Kandy', 'Ella', '2024-12-21', 'First', 50, 42, true, 24489.60),
(107, 'Colombo', 'Kandy', '2025-05-27', 'Third', 120, 97, false, 14877.45),
(108, 'Ella', 'Colombo', '2024-08-06', 'Second', 80, 64, true, 19723.20),
(106, 'Kandy', 'Ella', '2025-01-16', 'First', 50, 43, false, 25374.90),
(104, 'Colombo', 'Ella', '2024-11-29', 'Third', 120, 102, true, 15453.00),
(105, 'Kandy', 'Colombo', '2025-04-05', 'Second', 80, 66, false, 19598.70),
(107, 'Ella', 'Kandy', '2024-06-18', 'First', 50, 44, true, 25423.56),
(108, 'Colombo', 'Kandy', '2025-02-21', 'Third', 120, 98, false, 14613.90),
(106, 'Ella', 'Colombo', '2024-10-23', 'Second', 80, 65, true, 19358.25),
(104, 'Kandy', 'Ella', '2025-05-11', 'First', 50, 41, false, 24054.60),
(105, 'Colombo', 'Kandy', '2024-08-27', 'Third', 120, 99, true, 15392.25),
(107, 'Ella', 'Colombo', '2025-03-17', 'Second', 80, 67, false, 19746.75),
(108, 'Kandy', 'Ella', '2024-12-09', 'First', 50, 45, true, 25992.00),
(106, 'Colombo', 'Kandy', '2025-04-30', 'Third', 120, 100, false, 15000.00),
(104, 'Ella', 'Colombo', '2024-07-24', 'Second', 80, 64, true, 19324.80),
(105, 'Kandy', 'Ella', '2025-01-08', 'First', 50, 42, false, 24024.00),
(107, 'Colombo', 'Kandy', '2024-09-14', 'Third', 120, 98, true, 15288.60),
(108, 'Ella', 'Colombo', '2025-05-22', 'Second', 80, 68, false, 19940.40),
(101, 'Colombo', 'Kandy', '2024-07-02', 'First', 50, 45, true, 24675.61),
(107, 'Colombo', 'Kandy', '2024-05-08', 'First', 50, 36, false, 19858.07),
(103, 'Ella', 'Colombo', '2024-07-28', 'Third', 120, 92, false, 13956.84),
(104, 'Colombo', 'Kandy', '2024-03-08', 'Third', 120, 85, false, 11901.02),
(101, 'Colombo', 'Kandy', '2024-12-22', 'First', 50, 45, false, 26041.06),
(106, 'Kandy', 'Ella', '2025-03-15', 'Second', 80, 59, true, 18257.44),
(108, 'Ella', 'Kandy', '2024-04-10', 'First', 50, 43, true, 26519.33),
(102, 'Ella', 'Colombo', '2024-02-11', 'Third', 120, 97, false, 14250.64),
(105, 'Kandy', 'Colombo', '2025-04-29', 'Second', 80, 76, true, 22055.97),
(106, 'Colombo', 'Ella', '2024-10-03', 'First', 50, 35, false, 19044.10),
(107, 'Ella', 'Kandy', '2025-05-12', 'Third', 120, 89, true, 13050.77),
(108, 'Colombo', 'Kandy', '2024-08-17', 'Second', 80, 63, false, 19196.93),
(105, 'Kandy', 'Ella', '2025-01-27', 'First', 50, 43, true, 25903.78),
(104, 'Ella', 'Colombo', '2024-05-18', 'Third', 120, 101, false, 16099.95),
(103, 'Colombo', 'Kandy', '2024-12-17', 'Second', 80, 71, true, 19846.55),
(102, 'Kandy', 'Ella', '2025-03-06', 'First', 50, 42, false, 25034.34),
(106, 'Colombo', 'Ella', '2024-06-26', 'Third', 120, 94, true, 13635.35),
(108, 'Kandy', 'Colombo', '2025-04-21', 'Second', 80, 65, false, 18453.77),
(107, 'Ella', 'Kandy', '2024-11-05', 'First', 50, 44, true, 26234.54),
(101, 'Colombo', 'Ella', '2024-09-24', 'Third', 120, 99, false, 15264.96),
(102, 'Kandy', 'Colombo', '2025-02-15', 'Second', 80, 67, true, 20561.11),
(106, 'Ella', 'Kandy', '2024-07-16', 'First', 50, 41, false, 22628.95),
(108, 'Colombo', 'Ella', '2024-12-30', 'Third', 120, 93, true, 14365.01),
(105, 'Kandy', 'Colombo', '2025-05-13', 'Second', 80, 64, false, 18072.17),
(104, 'Ella', 'Kandy', '2024-04-04', 'First', 50, 40, true, 22851.98),
(103, 'Colombo', 'Ella', '2024-10-24', 'Third', 120, 86, false, 12510.57),
(101, 'Kandy', 'Colombo', '2025-03-22', 'Second', 80, 63, true, 18633.03),
(107, 'Ella', 'Colombo', '2024-08-12', 'First', 50, 39, false, 22252.45),
(106, 'Colombo', 'Kandy', '2025-01-09', 'Third', 120, 87, true, 14232.12),
(103, 'Kandy', 'Ella', '2024-06-23', 'Second', 80, 60, false, 18222.51),
(102, 'Colombo', 'Kandy', '2024-11-18', 'First', 50, 38, true, 20794.36),
(105, 'Ella', 'Colombo', '2025-04-06', 'Third', 120, 92, false, 14050.86),
(104, 'Kandy', 'Ella', '2024-09-01', 'Second', 80, 66, true, 19066.24),
(106, 'Colombo', 'Ella', '2025-02-12', 'First', 50, 40, false, 23042.94),
(108, 'Kandy', 'Colombo', '2024-07-11', 'Third', 120, 84, true, 12127.65),
(107, 'Ella', 'Kandy', '2024-12-31', 'Second', 80, 61, false, 18703.12),
(105, 'Colombo', 'Kandy', '2025-05-28', 'First', 50, 39, true, 23566.42),
(101, 'Ella', 'Colombo', '2024-04-20', 'Third', 120, 90, false, 13195.58),
(103, 'Kandy', 'Ella', '2024-10-09', 'Second', 80, 64, true, 20248.43),
(106, 'Colombo', 'Kandy', '2025-01-15', 'First', 50, 41, false, 24107.73),
(107, 'Ella', 'Colombo', '2024-08-22', 'Third', 120, 87, true, 12632.59),
(104, 'Kandy', 'Ella', '2025-02-03', 'Second', 80, 62, false, 19058.24),
(108, 'Colombo', 'Kandy', '2024-05-26', 'First', 50, 43, true, 26162.83),
(102, 'Ella', 'Colombo', '2024-11-10', 'Third', 120, 88, false, 13755.16),
(101, 'Kandy', 'Ella', '2025-03-30', 'Second', 80, 65, true, 19753.80),
(106, 'Colombo', 'Ella', '2024-07-31', 'First', 50, 42, false, 23562.60),
(108, 'Kandy', 'Colombo', '2025-01-19', 'Third', 120, 89, true, 13642.95),
(103, 'Kandy', 'Ella', '2024-10-14', 'Third', 120, 77, false, 10609.54),
(108, 'Ella', 'Colombo', '2025-02-08', 'Second', 80, 59, false, 17809.45),
(108, 'Ella', 'Kandy', '2025-05-15', 'Second', 80, 72, true, 22068.59),
(107, 'Kandy', 'Ella', '2024-09-06', 'Second', 80, 61, false, 18462.89),
(106, 'Ella', 'Kandy', '2025-03-31', 'Third', 120, 101, false, 14935.38),
(104, 'Kandy', 'Colombo', '2024-09-13', 'First', 50, 39, true, 21848.97),
(103, 'Colombo', 'Ella', '2025-01-07', 'Second', 80, 67, false, 19322.88),
(102, 'Kandy', 'Colombo', '2025-04-08', 'Third', 120, 95, true, 14631.75),
(105, 'Ella', 'Kandy', '2024-03-14', 'First', 50, 41, false, 23427.93),
(101, 'Colombo', 'Kandy', '2025-01-17', 'Second', 80, 65, true, 20283.86),
(108, 'Kandy', 'Ella', '2024-07-10', 'Third', 120, 92, false, 14057.85),
(105, 'Colombo', 'Ella', '2024-11-24', 'First', 50, 47, true, 26034.99),
(104, 'Ella', 'Kandy', '2025-04-29', 'Second', 80, 63, false, 18240.60),
(106, 'Colombo', 'Kandy', '2024-10-08', 'Third', 120, 88, true, 13578.84),
(107, 'Ella', 'Colombo', '2025-03-21', 'First', 50, 46, false, 25954.70),
(103, 'Kandy', 'Ella', '2024-08-04', 'Second', 80, 68, true, 20700.00),
(102, 'Colombo', 'Kandy', '2025-02-27', 'Third', 120, 94, false, 14430.75),
(101, 'Ella', 'Colombo', '2024-05-17', 'First', 50, 40, true, 22632.00),
(105, 'Kandy', 'Ella', '2024-10-22', 'Second', 80, 61, false, 17745.06),
(107, 'Colombo', 'Ella', '2025-04-04', 'Third', 120, 87, true, 13365.00),
(104, 'Ella', 'Kandy', '2024-09-25', 'First', 50, 44, false, 24883.08),
(108, 'Colombo', 'Kandy', '2025-03-13', 'Second', 80, 69, true, 19797.39),
(106, 'Kandy', 'Ella', '2024-07-01', 'Third', 120, 93, false, 14374.95),
(103, 'Ella', 'Colombo', '2024-12-16', 'First', 50, 41, true, 24607.80),
(102, 'Kandy', 'Ella', '2025-05-07', 'Second', 80, 67, false, 19149.90),
(101, 'Colombo', 'Kandy', '2024-03-31', 'Third', 120, 92, true, 14212.80),
(105, 'Ella', 'Colombo', '2024-08-22', 'First', 50, 42, false, 24660.00),
(107, 'Kandy', 'Ella', '2025-01-30', 'Second', 80, 68, true, 20577.36),
(104, 'Colombo', 'Kandy', '2024-10-31', 'Third', 120, 91, false, 13223.65),
(106, 'Ella', 'Colombo', '2025-04-25', 'First', 50, 43, true, 24003.51),
(108, 'Kandy', 'Ella', '2024-06-08', 'Second', 80, 64, false, 19516.80),
(103, 'Colombo', 'Kandy', '2024-11-13', 'Third', 120, 89, true, 13923.00),
(102, 'Ella', 'Colombo', '2025-05-25', 'First', 50, 40, false, 23184.00),
(101, 'Kandy', 'Ella', '2024-12-14', 'Second', 80, 66, true, 19481.16),
(105, 'Colombo', 'Kandy', '2024-04-18', 'Third', 120, 92, false, 13903.29),
(107, 'Ella', 'Colombo', '2024-09-09', 'First', 50, 41, true, 23796.90),
(104, 'Kandy', 'Ella', '2025-03-03', 'Second', 80, 67, false, 19596.69),
(106, 'Colombo', 'Kandy', '2024-08-28', 'Third', 120, 88, true, 13068.72),
(108, 'Ella', 'Colombo', '2025-02-22', 'First', 50, 46, false, 26244.06),
(103, 'Kandy', 'Ella', '2024-07-18', 'Second', 80, 65, true, 18837.45),
(102, 'Colombo', 'Kandy', '2024-12-01', 'Third', 120, 91, false, 14124.15),
(101, 'Ella', 'Colombo', '2025-05-10', 'First', 50, 39, true, 23354.16),
(105, 'Kandy', 'Ella', '2024-11-26', 'Second', 80, 64, false, 19778.88),
(107, 'Colombo', 'Kandy', '2025-04-16', 'Third', 120, 85, true, 13239.00),
(104, 'Ella', 'Kandy', '2024-10-19', 'First', 50, 42, false, 24786.84),
(106, 'Colombo', 'Ella', '2025-03-08', 'Second', 80, 68, true, 19558.17),
(108, 'Kandy', 'Colombo', '2024-06-30', 'Third', 120, 99, false, 15392.25),
(103, 'Ella', 'Kandy', '2024-11-11', 'First', 50, 40, true, 22867.20),
(102, 'Colombo', 'Ella', '2025-05-06', 'Second', 80, 67, false, 19358.04),
(101, 'Kandy', 'Colombo', '2024-10-11', 'Third', 120, 91, true, 13423.65),
(105, 'Ella', 'Kandy', '2025-04-01', 'First', 50, 41, false, 25068.90),
(107, 'Colombo', 'Kandy', '2024-08-16', 'Second', 80, 65, true, 19435.80),
(104, 'Ella', 'Colombo', '2025-02-06', 'Third', 120, 88, false, 13458.84),
(106, 'Kandy', 'Ella', '2024-12-25', 'First', 50, 42, true, 24024.00),
(108, 'Colombo', 'Kandy', '2025-05-21', 'Second', 80, 66, false, 19140.72),
(103, 'Ella', 'Colombo', '2024-10-06', 'Third', 120, 89, true, 13365.00),
(102, 'Kandy', 'Ella', '2025-03-25', 'First', 50, 41, false, 24156.90),
(101, 'Colombo', 'Kandy', '2024-09-12', 'Second', 80, 63, true, 18943.86),
(105, 'Ella', 'Colombo', '2025-01-13', 'Third', 120, 87, false, 13225.50),
(107, 'Kandy', 'Ella', '2024-07-25', 'First', 50, 40, true, 24348.00),
(104, 'Colombo', 'Ella', '2024-12-08', 'Second', 80, 62, false, 19119.96),
(106, 'Kandy', 'Colombo', '2025-05-16', 'Third', 120, 90, true, 14107.50),
(108, 'Ella', 'Kandy', '2024-11-02', 'First', 50, 39, false, 22176.30),
(103, 'Colombo', 'Ella', '2025-04-23', 'Second', 80, 61, true, 18653.13),
(102, 'Kandy', 'Colombo', '2024-10-17', 'Third', 120, 86, false, 13161.60),
(101, 'Ella', 'Kandy', '2025-03-14', 'First', 50, 40, true, 22963.20),
(105, 'Colombo', 'Kandy', '2024-08-07', 'Second', 80, 59, false, 18153.15),
(107, 'Ella', 'Colombo', '2025-01-04', 'Third', 120, 84, true, 12879.84),
(104, 'Kandy', 'Ella', '2024-11-22', 'First', 50, 38, false, 21584.82),
(106, 'Colombo', 'Kandy', '2025-05-09', 'Second', 80, 60, true, 18288.00),
(108, 'Ella', 'Colombo', '2024-03-15', 'Third', 120, 82, false, 12594.00),
(103, 'Kandy', 'Ella', '2024-09-28', 'First', 50, 37, true, 20848.38),
(102, 'Colombo', 'Kandy', '2025-02-19', 'Second', 80, 58, false, 17139.99),
(101, 'Ella', 'Colombo', '2024-12-11', 'Third', 120, 79, true, 12343.65),
(105, 'Kandy', 'Ella', '2025-05-30', 'First', 50, 36, false, 19905.72),
(107, 'Colombo', 'Kandy', '2024-11-16', 'Second', 80, 55, true, 16017.45),
(104, 'Ella', 'Colombo', '2025-04-11', 'Third', 120, 77, false, 11718.30),
(106, 'Kandy', 'Ella', '2024-10-27', 'First', 50, 33, true, 20009.76),
(108, 'Colombo', 'Kandy', '2025-03-29', 'Second', 80, 51, false, 15721.29),
(103, 'Ella', 'Colombo', '2024-09-05', 'Third', 120, 74, true, 11517.36),
(102, 'Kandy', 'Ella', '2025-01-22', 'First', 50, 31, false, 18097.80),
(101, 'Colombo', 'Kandy', '2024-12-07', 'Second', 80, 48, true, 13726.08),
(105, 'Ella', 'Colombo', '2025-05-19', 'Third', 120, 69, false, 9903.90),
(107, 'Kandy', 'Ella', '2024-04-26', 'First', 50, 28, true, 16180.08),
(104, 'Colombo', 'Ella', '2024-10-13', 'Second', 80, 44, false, 13194.00),
(106, 'Kandy', 'Colombo', '2025-03-07', 'Third', 120, 66, true, 9586.50),
(108, 'Ella', 'Kandy', '2024-08-29', 'First', 50, 25, false, 14025.00),
(103, 'Colombo', 'Kandy', '2025-02-13', 'Second', 80, 41, true, 12348.81),
(102, 'Kandy', 'Ella', '2024-07-23', 'Third', 120, 63, false, 8943.90),
(101, 'Ella', 'Colombo', '2024-10-30', 'First', 50, 22, true, 12348.00),
(105, 'Kandy', 'Ella', '2025-04-14', 'Second', 80, 38, false, 11181.96),
(107, 'Colombo', 'Kandy', '2024-09-21', 'Third', 120, 60, true, 9000.00),
(104, 'Ella', 'Colombo', '2025-02-25', 'First', 50, 19, false, 10969.20),
(106, 'Kandy', 'Ella', '2024-12-20', 'Second', 80, 35, true, 10201.55),
(108, 'Colombo', 'Kandy', '2025-05-31', 'Third', 120, 57, false, 8550.00),
(103, 'Ella', 'Colombo', '2024-04-24', 'First', 50, 16, true, 9126.36),
(102, 'Kandy', 'Ella', '2024-10-16', 'Second', 80, 32, false, 9679.92),
(101, 'Colombo', 'Kandy', '2025-03-11', 'Third', 120, 54, true, 8100.00),
(105, 'Ella', 'Colombo', '2024-09-08', 'First', 50, 13, false, 7280.10),
(107, 'Kandy', 'Ella', '2025-01-26', 'Second', 80, 29, true, 8397.63),
(104, 'Colombo', 'Kandy', '2024-11-09', 'Third', 120, 51, false, 7650.00),
(106, 'Ella', 'Colombo', '2025-04-27', 'First', 50, 10, true, 5490.00),
(108, 'Kandy', 'Ella', '2024-10-26', 'Second', 80, 26, false, 7726.56),
(103, 'Colombo', 'Kandy', '2025-02-10', 'Third', 120, 48, true, 7236.00),
(102, 'Ella', 'Colombo', '2024-07-31', 'First', 50, 7, false, 3897.00),
(101, 'Kandy', 'Ella', '2024-12-29', 'Second', 80, 23, true, 6780.51),
(105, 'Colombo', 'Kandy', '2025-05-08', 'Third', 120, 45, false, 6817.56),
(107, 'Ella', 'Colombo', '2024-11-23', 'First', 50, 4, true, 2323.56),
(104, 'Kandy', 'Ella', '2025-04-20', 'Second', 80, 20, false, 5712.00),
(106, 'Colombo', 'Kandy', '2024-10-02', 'Third', 120, 42, true, 6426.30),
(108, 'Ella', 'Colombo', '2025-03-26', 'First', 50, 1, false, 588.30),
(103, 'Kandy', 'Ella', '2024-09-03', 'Second', 80, 17, true, 5095.53),
(102, 'Colombo', 'Kandy', '2025-02-01', 'Third', 120, 39, false, 5691.60),
(101, 'Ella', 'Kandy', '2024-12-15', 'First', 50, 46, true, 27600.00),
(105, 'Colombo', 'Ella', '2025-05-24', 'Second', 80, 75, false, 21060.00),
(107, 'Kandy', 'Colombo', '2024-04-10', 'Third', 120, 111, false, 17759.62),
(106, 'Ella', 'Colombo', '2024-08-01', 'Second', 80, 70, false, 22974.36),
(102, 'Kandy', 'Ella', '2024-06-03', 'Second', 80, 62, true, 16777.45),
(101, 'Kandy', 'Ella', '2025-03-09', 'First', 50, 44, false, 24869.45),
(103, 'Kandy', 'Colombo', '2024-07-28', 'Third', 120, 106, true, 17098.74),