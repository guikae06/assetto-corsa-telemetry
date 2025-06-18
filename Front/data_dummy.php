<?php
header('Content-Type: application/json');

// Simuleer willekeurige demo-data
$data = [
    'rpm' => rand(800, 9500),
    'speed_kmh' => rand(0, 320),
    'turbo' => round(mt_rand(0, 200) / 100, 2),
    'gear' => rand(0, 6),
    'throttle' => round(mt_rand(0, 100) / 100, 2),
    'brake' => round(mt_rand(0, 100) / 100, 2),
    'lap_time_ms' => rand(85000, 130000),
    'last_lap_ms' => rand(85000, 130000),
    'best_lap_ms' => rand(84000, 128000),
    'lap_count' => rand(0, 10),
    'track_name' => 'Spa',
    'datetime' => date('Y-m-d H:i:s')
];

echo json_encode($data);
