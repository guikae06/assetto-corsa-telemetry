

CREATE TABLE IF NOT EXISTS telemetry (
  id SERIAL PRIMARY KEY,
  rpm INTEGER,
  turbo DOUBLE PRECISION,
  speed_kmh DOUBLE PRECISION,
  gear INTEGER,
  throttle DOUBLE PRECISION,
  brake DOUBLE PRECISION,
  lap_time_ms INTEGER,
  last_lap_ms INTEGER,
  lap_count INTEGER,
  best_lap_ms INTEGER,
  track_name TEXT,
  datetime TIMESTAMPTZ DEFAULT now()
);

GRANT INSERT, SELECT, UPDATE, DELETE ON TABLE telemetry TO webtechuser;