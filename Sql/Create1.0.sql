CREATE TABLE user_account (
    user_account_id SERIAL PRIMARY KEY,
    user_account_name VARCHAR(100) NOT NULL,
    user_account_email VARCHAR(255) NOT NULL UNIQUE,
    user_account_password_hash TEXT NOT NULL,
	user_account_role VARCHAR(20) NOT NULL DEFAULT 'user',
    user_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	CHECK (user_account_role IN ('user','admin'))
);

CREATE TABLE plant_type (
    plant_type_id SERIAL PRIMARY KEY,
    plant_type_name VARCHAR(150) NOT NULL,
    plant_type_min_moisture INT NOT NULL,
    plant_type_max_moisture INT NOT NULL,
	CHECK (plant_type_min_moisture <= plant_type_max_moisture)
);

CREATE TABLE device (
    device_id SERIAL PRIMARY KEY,
    device_code VARCHAR(100) NOT NULL UNIQUE,
    device_name VARCHAR(100)
);

CREATE TABLE plant (
    plant_id SERIAL PRIMARY KEY,
    user_account_id INT NOT NULL REFERENCES user_account(user_account_id),
    plant_type_id INT NOT NULL REFERENCES plant_type(plant_type_id),
    device_id INT UNIQUE REFERENCES device(device_id),
    plant_name VARCHAR(150) NOT NULL,
    plant_location_label VARCHAR(150),
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE sensor_reading (
    sensor_reading_id SERIAL PRIMARY KEY,
    device_id INT NOT NULL REFERENCES device(device_id),
    sensor_reading_moisture_percent INT NOT NULL,
    sensor_reading_latitude DECIMAL(9,6),
    sensor_reading_longitude DECIMAL(9,6),
    sensor_reading_recorded_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
	CHECK (sensor_reading_moisture_percent BETWEEN 0 AND 100)
);