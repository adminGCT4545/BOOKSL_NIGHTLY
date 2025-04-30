-- Setup PostgreSQL triggers and functions for live updates

-- Create a function to notify when train_journeys table changes
CREATE OR REPLACE FUNCTION notify_journey_changes()
RETURNS trigger AS $$
DECLARE
  notification json;
BEGIN
  -- Create a JSON notification with the changed data
  IF (TG_OP = 'DELETE') THEN
    notification = json_build_object(
      'operation', TG_OP,
      'journey_id', OLD.journey_id
    );
  ELSE
    notification = json_build_object(
      'operation', TG_OP,
      'journey_id', NEW.journey_id,
      'train_id', NEW.train_id,
      'departure_city', NEW.departure_city,
      'arrival_city', NEW.arrival_city,
      'journey_date', NEW.journey_date,
      'class', NEW.class,
      'total_seats', NEW.total_seats,
      'reserved_seats', NEW.reserved_seats,
      'is_delayed', NEW.is_delayed,
      'revenue', NEW.revenue
    );
  END IF;

  -- Send notification
  PERFORM pg_notify('journey_changes', notification::text);
  
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Create a trigger for INSERT operations
DROP TRIGGER IF EXISTS journey_insert_trigger ON train_journeys;
CREATE TRIGGER journey_insert_trigger
AFTER INSERT ON train_journeys
FOR EACH ROW
EXECUTE FUNCTION notify_journey_changes();

-- Create a trigger for UPDATE operations
DROP TRIGGER IF EXISTS journey_update_trigger ON train_journeys;
CREATE TRIGGER journey_update_trigger
AFTER UPDATE ON train_journeys
FOR EACH ROW
EXECUTE FUNCTION notify_journey_changes();

-- Create a trigger for DELETE operations
DROP TRIGGER IF EXISTS journey_delete_trigger ON train_journeys;
CREATE TRIGGER journey_delete_trigger
AFTER DELETE ON train_journeys
FOR EACH ROW
EXECUTE FUNCTION notify_journey_changes();

-- Create a function to notify when train_schedules table changes
CREATE OR REPLACE FUNCTION notify_schedule_changes()
RETURNS trigger AS $$
DECLARE
  notification json;
BEGIN
  -- Create a JSON notification with the changed data
  IF (TG_OP = 'DELETE') THEN
    notification = json_build_object(
      'operation', TG_OP,
      'train_id', OLD.train_id
    );
  ELSE
    notification = json_build_object(
      'operation', TG_OP,
      'train_id', NEW.train_id,
      'scheduled_time', NEW.scheduled_time,
      'default_delay_minutes', NEW.default_delay_minutes
    );
  END IF;

  -- Send notification
  PERFORM pg_notify('schedule_changes', notification::text);
  
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Create a trigger for INSERT operations
DROP TRIGGER IF EXISTS schedule_insert_trigger ON train_schedules;
CREATE TRIGGER schedule_insert_trigger
AFTER INSERT ON train_schedules
FOR EACH ROW
EXECUTE FUNCTION notify_schedule_changes();

-- Create a trigger for UPDATE operations
DROP TRIGGER IF EXISTS schedule_update_trigger ON train_schedules;
CREATE TRIGGER schedule_update_trigger
AFTER UPDATE ON train_schedules
FOR EACH ROW
EXECUTE FUNCTION notify_schedule_changes();

-- Create a trigger for DELETE operations
DROP TRIGGER IF EXISTS schedule_delete_trigger ON train_schedules;
CREATE TRIGGER schedule_delete_trigger
AFTER DELETE ON train_schedules
FOR EACH ROW
EXECUTE FUNCTION notify_schedule_changes();

-- Create a function to notify when trains table changes
CREATE OR REPLACE FUNCTION notify_train_changes()
RETURNS trigger AS $$
DECLARE
  notification json;
BEGIN
  -- Create a JSON notification with the changed data
  IF (TG_OP = 'DELETE') THEN
    notification = json_build_object(
      'operation', TG_OP,
      'train_id', OLD.train_id
    );
  ELSE
    notification = json_build_object(
      'operation', TG_OP,
      'train_id', NEW.train_id,
      'train_name', NEW.train_name
    );
  END IF;

  -- Send notification
  PERFORM pg_notify('train_changes', notification::text);
  
  RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Create a trigger for INSERT operations
DROP TRIGGER IF EXISTS train_insert_trigger ON trains;
CREATE TRIGGER train_insert_trigger
AFTER INSERT ON trains
FOR EACH ROW
EXECUTE FUNCTION notify_train_changes();

-- Create a trigger for UPDATE operations
DROP TRIGGER IF EXISTS train_update_trigger ON trains;
CREATE TRIGGER train_update_trigger
AFTER UPDATE ON trains
FOR EACH ROW
EXECUTE FUNCTION notify_train_changes();

-- Create a trigger for DELETE operations
DROP TRIGGER IF EXISTS train_delete_trigger ON trains;
CREATE TRIGGER train_delete_trigger
AFTER DELETE ON trains
FOR EACH ROW
EXECUTE FUNCTION notify_train_changes();
