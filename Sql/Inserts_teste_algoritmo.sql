Select * 
From sensor_reading

1) Descida constante ao longo de 2h+ com 10 pontos
truncate table sensor_reading RESTART IDENTITY,
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 72, NOW() - INTERVAL '135 minutes'),
(1, 71, NOW() - INTERVAL '120 minutes'),
(1, 69, NOW() - INTERVAL '105 minutes'),
(1, 67, NOW() - INTERVAL '90 minutes'),
(1, 65, NOW() - INTERVAL '75 minutes'),
(1, 63, NOW() - INTERVAL '60 minutes'),
(1, 61, NOW() - INTERVAL '45 minutes'),
(1, 58, NOW() - INTERVAL '30 minutes'),
(1, 55, NOW() - INTERVAL '15 minutes'),
(1, 51, NOW());	
2) Spike ≥ 10% na última leitura
truncate table sensor_reading RESTART IDENTITY
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 60, NOW() - INTERVAL '45 minutes'),
(1, 57, NOW() - INTERVAL '30 minutes'),
(1, 54, NOW() - INTERVAL '15 minutes'),
(1, 42, NOW() - INTERVAL '5 minutes'),
(1, 78, NOW());
3) Humidade a subir (slope positivo)
truncate table sensor_reading RESTART IDENTITY
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 41, NOW() - INTERVAL '50 minutes'),
(1, 43, NOW() - INTERVAL '40 minutes'),
(1, 46, NOW() - INTERVAL '30 minutes'),
(1, 49, NOW() - INTERVAL '20 minutes'),
(1, 52, NOW() - INTERVAL '10 minutes'),
(1, 55, NOW());
4) Menos de 3 leituras válidas
truncate table sensor_reading RESTART IDENTITY
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 60, NOW() - INTERVAL '10 minutes'),
(1, 58, NOW());
5) Dados muito ruidosos / sem tendência clara
truncate table sensor_reading RESTART IDENTITY
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 55, NOW() - INTERVAL '50 minutes'),
(1, 72, NOW() - INTERVAL '40 minutes'),
(1, 41, NOW() - INTERVAL '30 minutes'),
(1, 68, NOW() - INTERVAL '20 minutes'),
(1, 43, NOW() - INTERVAL '10 minutes'),
(1, 45, NOW());

6) Taxa de secagem extremamente lenta
truncate table sensor_reading RESTART IDENTITY
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 55, NOW() - INTERVAL '50 minutes'),
(1, 54.9, NOW() - INTERVAL '40 minutes'),
(1, 54.8, NOW() - INTERVAL '30 minutes'),
(1, 54.7, NOW() - INTERVAL '20 minutes'),
(1, 54.6, NOW() - INTERVAL '10 minutes'),
(1, 54.5, NOW());
7) Poucos pontos, intervalo curto
truncate table sensor_reading RESTART IDENTITY
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 58, NOW() - INTERVAL '20 minutes'),
(1, 55, NOW() - INTERVAL '10 minutes'),
(1, 52, NOW());
8) Taxa muito alta de secagem
truncate table sensor_reading RESTART IDENTITY
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 80, NOW() - INTERVAL '50 minutes'),
(1, 72, NOW() - INTERVAL '40 minutes'),
(1, 64, NOW() - INTERVAL '30 minutes'),
(1, 56, NOW() - INTERVAL '20 minutes'),
(1, 49, NOW() - INTERVAL '10 minutes'),
(1, 42, NOW());
9) Taxa baixa mas dentro do limite de 48h
truncate table sensor_reading RESTART IDENTITY
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 65, NOW() - INTERVAL '100 minutes'),
(1, 64, NOW() - INTERVAL '80 minutes'),
(1, 63, NOW() - INTERVAL '60 minutes'),
(1, 62, NOW() - INTERVAL '40 minutes'),
(1, 61, NOW() - INTERVAL '20 minutes'),
(1, 60, NOW());
10) Maioria dos dados consistente + 2 leituras absurdas
truncate table sensor_reading RESTART IDENTITY
INSERT INTO sensor_reading (device_id, sensor_reading_moisture_percent, sensor_reading_recorded_at) VALUES
(1, 62, NOW() - INTERVAL '90 minutes'),
(1, 99, NOW() - INTERVAL '75 minutes'),
(1, 59, NOW() - INTERVAL '60 minutes'),
(1, 57, NOW() - INTERVAL '45 minutes'),
(1, 5,  NOW() - INTERVAL '30 minutes'),
(1, 54, NOW() - INTERVAL '15 minutes'),
(1, 52, NOW());


CREATE INDEX idx_sensor_device_time
ON sensor_reading (device_id, sensor_reading_recorded_at DESC);

