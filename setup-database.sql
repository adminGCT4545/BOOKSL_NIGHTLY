-- Create tables for BookSL Train Dashboard

-- Train table
CREATE TABLE IF NOT EXISTS trains (
  train_id INTEGER PRIMARY KEY,
  train_name VARCHAR(100) NOT NULL
);

-- Train schedules table
CREATE TABLE IF NOT EXISTS train_schedules (
  train_id INTEGER PRIMARY KEY REFERENCES trains(train_id),
  scheduled_time TIME NOT NULL,
  default_delay_minutes INTEGER DEFAULT 0
);

-- Train journeys table
CREATE TABLE IF NOT EXISTS train_journeys (
  journey_id SERIAL PRIMARY KEY,
  train_id INTEGER NOT NULL REFERENCES trains(train_id),
  departure_city VARCHAR(100) NOT NULL,
  arrival_city VARCHAR(100) NOT NULL,
  journey_date DATE NOT NULL,
  class VARCHAR(20) NOT NULL,
  total_seats INTEGER NOT NULL,
  reserved_seats INTEGER NOT NULL,
  is_delayed BOOLEAN DEFAULT FALSE,
  revenue DECIMAL(10, 2) NOT NULL
);

-- Insert train data
INSERT INTO trains (train_id, train_name) VALUES
  (101, 'Perali Express'),
  (102, 'Udarata Menike'),
  (103, 'Kandy Intercity'),
  (104, 'Ella Odyssey'),
  (105, 'Rajarata Express'),
  (106, 'Yal Devi'),
  (107, 'Podi Menike'),
  (108, 'Ruhunu Kumari')
ON CONFLICT (train_id) DO UPDATE SET train_name = EXCLUDED.train_name;

-- Insert train schedules
INSERT INTO train_schedules (train_id, scheduled_time, default_delay_minutes) VALUES
  (101, '06:00:00', 5),
  (102, '07:30:00', 8),
  (103, '14:00:00', 0),
  (104, '16:30:00', 15),
  (105, '08:15:00', 10),
  (106, '10:45:00', 0),
  (107, '12:30:00', 7),
  (108, '18:00:00', 12)
ON CONFLICT (train_id) DO UPDATE SET 
  scheduled_time = EXCLUDED.scheduled_time,
  default_delay_minutes = EXCLUDED.default_delay_minutes;

-- Insert train journey data
INSERT INTO train_journeys (journey_id, train_id, departure_city, arrival_city, journey_date, class, total_seats, reserved_seats, is_delayed, revenue) VALUES
  -- January 2025 data
  (1, 101, 'Colombo', 'Kandy', '2025-01-05', 'First', 50, 45, TRUE, 22500.00),
  (2, 101, 'Kandy', 'Colombo', '2025-01-05', 'First', 50, 48, FALSE, 24000.00),
  (3, 102, 'Colombo', 'Ella', '2025-01-05', 'Second', 80, 65, FALSE, 19500.00),
  (4, 102, 'Ella', 'Colombo', '2025-01-05', 'Second', 80, 72, TRUE, 21600.00),
  (5, 103, 'Kandy', 'Ella', '2025-01-06', 'Third', 120, 95, FALSE, 14250.00),
  (6, 103, 'Ella', 'Kandy', '2025-01-06', 'Third', 120, 110, FALSE, 16500.00),
  (7, 104, 'Colombo', 'Kandy', '2025-01-06', 'Second', 80, 78, TRUE, 23400.00),
  (8, 104, 'Kandy', 'Colombo', '2025-01-06', 'Second', 80, 76, FALSE, 22800.00),
  (9, 101, 'Colombo', 'Ella', '2025-01-07', 'First', 50, 42, FALSE, 25200.00),
  (10, 101, 'Ella', 'Colombo', '2025-01-07', 'First', 50, 47, TRUE, 28200.00),
  (11, 102, 'Kandy', 'Ella', '2025-01-07', 'Third', 120, 85, FALSE, 12750.00),
  (12, 102, 'Ella', 'Kandy', '2025-01-07', 'Third', 120, 92, TRUE, 13800.00),
  
  -- February 2025 data
  (13, 103, 'Colombo', 'Kandy', '2025-02-10', 'First', 50, 49, FALSE, 24500.00),
  (14, 103, 'Kandy', 'Colombo', '2025-02-10', 'First', 50, 50, FALSE, 25000.00),
  (15, 104, 'Colombo', 'Ella', '2025-02-10', 'Second', 80, 64, TRUE, 19200.00),
  (16, 104, 'Ella', 'Colombo', '2025-02-10', 'Second', 80, 70, FALSE, 21000.00),
  (17, 101, 'Kandy', 'Ella', '2025-02-11', 'Third', 120, 98, FALSE, 14700.00),
  (18, 101, 'Ella', 'Kandy', '2025-02-11', 'Third', 120, 105, FALSE, 15750.00),
  
  -- 2024 data
  (19, 105, 'Ella', 'Colombo', '2024-01-11', 'Third', 120, 113, FALSE, 18211.05),
  (20, 101, 'Kandy', 'Colombo', '2024-07-22', 'Third', 120, 74, FALSE, 10718.51),
  (21, 105, 'Colombo', 'Ella', '2024-06-15', 'Third', 120, 81, FALSE, 12478.04),
  (22, 102, 'Kandy', 'Colombo', '2024-06-15', 'Third', 120, 72, FALSE, 11056.98),
  (23, 103, 'Ella', 'Colombo', '2024-06-15', 'First', 50, 37, TRUE, 24074.57),
  (24, 103, 'Ella', 'Kandy', '2024-06-15', 'Second', 80, 54, FALSE, 14909.75),
  (25, 103, 'Kandy', 'Colombo', '2024-06-15', 'Third', 120, 98, TRUE, 13754.50),
  (26, 108, 'Kandy', 'Colombo', '2024-06-15', 'Second', 80, 60, TRUE, 17081.62),
  (27, 102, 'Ella', 'Colombo', '2024-06-15', 'Third', 120, 106, FALSE, 14861.04),
  (28, 103, 'Ella', 'Kandy', '2024-06-15', 'Second', 80, 70, FALSE, 19544.18),
  (29, 105, 'Ella', 'Kandy', '2024-06-15', 'First', 50, 48, TRUE, 30462.16),
  (30, 103, 'Kandy', 'Colombo', '2024-06-15', 'First', 50, 47, FALSE, 26636.58),
  
  -- Additional data
  (31, 106, 'Kandy', 'Ella', '2024-03-22', 'Third', 120, 102, TRUE, 15956.40),
  (32, 104, 'Ella', 'Colombo', '2024-07-14', 'Second', 80, 65, FALSE, 20345.85),
  (33, 107, 'Colombo', 'Kandy', '2024-10-05', 'First', 50, 43, TRUE, 27144.90),
  (34, 108, 'Kandy', 'Ella', '2024-11-18', 'Third', 120, 96, TRUE, 15342.72),
  (35, 105, 'Ella', 'Colombo', '2024-05-29', 'Second', 80, 75, FALSE, 21956.25),
  (36, 107, 'Colombo', 'Ella', '2025-03-12', 'First', 50, 47, FALSE, 28752.06),
  (37, 104, 'Ella', 'Kandy', '2025-01-23', 'Third', 120, 108, TRUE, 17562.60),
  (38, 106, 'Kandy', 'Colombo', '2025-02-09', 'Second', 80, 62, FALSE, 18937.50),
  (39, 105, 'Colombo', 'Ella', '2024-12-24', 'First', 50, 48, TRUE, 29760.96),
  (40, 108, 'Kandy', 'Colombo', '2025-04-07', 'Third', 120, 97, FALSE, 15941.25)
ON CONFLICT (journey_id) DO UPDATE SET
  train_id = EXCLUDED.train_id,
  departure_city = EXCLUDED.departure_city,
  arrival_city = EXCLUDED.arrival_city,
  journey_date = EXCLUDED.journey_date,
  class = EXCLUDED.class,
  total_seats = EXCLUDED.total_seats,
  reserved_seats = EXCLUDED.reserved_seats,
  is_delayed = EXCLUDED.is_delayed,
  revenue = EXCLUDED.revenue;
