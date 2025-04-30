-- Create passengers table for BookSL Train Dashboard
CREATE TABLE IF NOT EXISTS passengers (
    rider_number INTEGER PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    originating_station VARCHAR(100) NOT NULL,
    most_visited_station VARCHAR(100) NOT NULL,
    spend_per_ride NUMERIC(10, 2) NOT NULL,
    monthly_total NUMERIC(10, 2) NOT NULL,
    usage_frequency INTEGER NOT NULL,
    uses_other_trains BOOLEAN NOT NULL,
    other_trains_used TEXT
);

-- Clear existing data to avoid duplicates
TRUNCATE TABLE passengers;

-- Generate 1000 passenger records using PL/pgSQL
DO $$
DECLARE
    train_stations TEXT[] := ARRAY[
        'Colombo Fort', 'Maradana', 'Mount Lavinia', 'Kandy', 'Galle', 
        'Negombo', 'Anuradhapura', 'Polonnaruwa', 'Badulla', 'Jaffna', 
        'Trincomalee', 'Batticaloa', 'Matara', 'Beliatta', 'Ella', 
        'Nanu Oya', 'Hatton', 'Polgahawela', 'Kurunegala', 'Vavuniya'
    ];
    other_trains TEXT[] := ARRAY[
        'Udarata Menike', 'Podi Menike', 'Yal Devi', 'Uttara Devi', 
        'Rajarata Rajina', 'Ruhunu Kumari', 'Samudra Devi', 'Galu Kumari', 
        'Sagarika', 'Udaya Devi', 'Meena Gaya', 'Night Mail', 'Denuwara Menike', 
        'Tikiri Menike', 'Senkadagala Menike', 'Intercity Express', 'Colombo Commuter'
    ];
    first_names TEXT[] := ARRAY[
        'Amal', 'Sunil', 'Kamal', 'Nimal', 'Saman', 'Chaminda', 'Dinesh', 'Lasith', 
        'Ruwan', 'Pradeep', 'Kumar', 'Ajith', 'Thilak', 'Mahesh', 'Nuwan', 'Chamara',
        'Rajiv', 'Anura', 'Thushara', 'Amila', 'Kumara', 'Duminda', 'Charith', 'Lakmal',
        'Malini', 'Kumari', 'Chamari', 'Nilmini', 'Dilrukshi', 'Sanduni', 'Ama', 'Subashini',
        'Thilini', 'Hashini', 'Nisansala', 'Nadeesha', 'Sachini', 'Nimesha', 'Chathurika', 'Dulani',
        'Rajapaksa', 'Muthiah', 'Perera', 'Karuna', 'Suriya', 'Thevani', 'Selva', 'Ganesan',
        'Ashok', 'Priyan', 'Vijay', 'Rajan', 'Shanthi', 'Kala', 'Tharini', 'Arunika'
    ];
    last_names TEXT[] := ARRAY[
        'Perera', 'Silva', 'Fernando', 'Dissanayake', 'Bandara', 'Ratnayake', 'Jayawardena', 'Gunawardena',
        'Rajapaksa', 'Seneviratne', 'Wickramasinghe', 'Karunaratne', 'Herath', 'Pathirana', 'Fonseka', 'Mendis',
        'Navaratnam', 'Kumaraswamy', 'Rajakaruna', 'Selvarajah', 'Thilakaratne', 'Cooray', 'Amarasinghe', 'Peiris',
        'Samaraweera', 'Nanayakkara', 'Chandrasekara', 'Wijesinghe', 'Sivarajah', 'Gunatilleke', 'Ranatunga', 'Weerasinghe',
        'Munasinghe', 'Balasuriya', 'Gunasekara', 'Rodrigo', 'Ramanathan', 'Weerakoon', 'Jayasuriya', 'Samaranayake',
        'Mudalige', 'Hettige', 'Kulatunga', 'Wijethunga', 'Vithanage', 'Kannangara', 'Liyanage', 'Kumara'
    ];
    
    i INTEGER;
    rider_num INTEGER;
    first_name TEXT;
    last_name TEXT;
    originating_station TEXT;
    most_visited_station TEXT;
    spend_per_ride NUMERIC;
    monthly_total NUMERIC;
    usage_frequency INTEGER;
    uses_other_trains BOOLEAN;
    other_trains_used TEXT;
    num_other_trains INTEGER;
    random_train TEXT;
    other_trains_list TEXT[];
    available_trains TEXT[];
    random_index INTEGER;
BEGIN
    -- Generate 1000 passenger records
    FOR rider_num IN 1..1000 LOOP
        -- Random first and last name
        first_name := first_names[1 + floor(random() * array_length(first_names, 1))];
        last_name := last_names[1 + floor(random() * array_length(last_names, 1))];
        
        -- Random originating station
        originating_station := train_stations[1 + floor(random() * array_length(train_stations, 1))];
        
        -- Random most visited station (different from originating)
        LOOP
            most_visited_station := train_stations[1 + floor(random() * array_length(train_stations, 1))];
            EXIT WHEN most_visited_station <> originating_station;
        END LOOP;
        
        -- Random usage frequency (1-30 per month)
        usage_frequency := 1 + floor(random() * 30);
        
        -- Random spend per ride (100-2000 LKR)
        spend_per_ride := 100 + floor(random() * 1901);
        
        -- Calculate monthly total
        monthly_total := spend_per_ride * usage_frequency;
        
        -- Determine if they use other trains (more frequent travelers more likely)
        uses_other_trains := random() < (usage_frequency / 40.0);
        
        -- Generate other trains list if applicable
        other_trains_list := ARRAY[]::TEXT[];
        IF uses_other_trains THEN
            num_other_trains := 1 + floor(random() * 3);
            available_trains := other_trains;
            
            FOR i IN 1..num_other_trains LOOP
                EXIT WHEN array_length(available_trains, 1) = 0;
                
                -- Pick a random train
                random_index := 1 + floor(random() * array_length(available_trains, 1));
                random_train := available_trains[random_index];
                
                -- Add to list
                other_trains_list := array_append(other_trains_list, random_train);
                
                -- Remove from available trains to avoid duplicates
                available_trains := array_remove(available_trains, random_train);
            END LOOP;
            
            -- Convert array to semicolon-separated string
            other_trains_used := array_to_string(other_trains_list, ';');
        ELSE
            other_trains_used := NULL;
        END IF;
        
        -- Insert the passenger record
        INSERT INTO passengers (
            rider_number, first_name, last_name, 
            originating_station, most_visited_station, 
            spend_per_ride, monthly_total, usage_frequency, 
            uses_other_trains, other_trains_used
        ) VALUES (
            rider_num, first_name, last_name,
            originating_station, most_visited_station,
            spend_per_ride, monthly_total, usage_frequency,
            uses_other_trains, other_trains_used
        );
    END LOOP;
END $$;
