<?php
header('Content-Type: application/json');

// Genereer dummy data
$dummy = [
  'rpm' => rand(1000, 9000),
  'turbo' => round(mt_rand(0, 20) / 10, 2),
  'speed_kmh' => rand(0, 300),
  'gear' => rand(1, 6),
  'throttle' => round(mt_rand(0, 100) / 100, 2),
  'brake' => round(mt_rand(0, 100) / 100, 2),
  'lap_time_ms' => rand(60000, 120000),
  'last_lap_ms' => rand(60000, 120000),
  'lap_count' => rand(1, 10),
  'best_lap_ms' => rand(58000, 119000),
  'track_name' => 'Spa-Francorchamps',
  'datetime' => date('c')  // ISO 8601 datetime
];

// Stuur de dummy data als JSON terug
echo json_encode($dummy);
