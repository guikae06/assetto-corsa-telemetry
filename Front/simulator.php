<?php
$url = 'http://localhost:8081/lib.php';

// 🚗 Startwaarden
$rpm = 2000;
$speed = 0;
$gear = 1;
$throttle = 0.4;
$brake = 0.0;
$lapCount = 1;
$track = ['Spa', 'Zolder', 'Monza', 'Red Bull Ring'][array_rand([0, 1, 2, 3])];
$lastLap = 85000;
$bestLap = $lastLap;

// ⏱ loop
while (true) {
    // ⛽ throttle licht laten fluctueren
    $throttle += (rand(-5, 5) / 100);
    $throttle = max(0.1, min(1.0, $throttle));

    // 🛑 soms beetje remmen
    $brake = (rand(0, 100) < 10) ? rand(10, 50) / 100 : 0.0;

    // 🚀 snelheid stijgt/daalt afhankelijk van throttle en brake
    $speed += ($throttle * 5) - ($brake * 10) + rand(-1, 1);
    $speed = max(0, min(280, $speed));

    // ⚙️ gear bepaalt op basis van speed
    if ($speed < 20) $gear = 1;
    elseif ($speed < 40) $gear = 2;
    elseif ($speed < 80) $gear = 3;
    elseif ($speed < 140) $gear = 4;
    elseif ($speed < 200) $gear = 5;
    else $gear = 6;

    // 🌀 rpm volgt speed & throttle
    $rpm = ($speed * 30) + ($throttle * 2000) + rand(-100, 100);
    $rpm = max(1000, min(9000, (int)$rpm));

    // ⏱ lap info
    $lapTime = rand(80000, 95000);
    $lapCount++;
    $lastLap = $lapTime;
    $bestLap = min($bestLap, $lapTime);
	// 📤 Data versturen
    $data = [
        'rpm' => (int)$rpm,
        'turbo' => round($throttle * 2, 2),
        'SpeedKMH' => (int)$speed,
        'gear' => $gear,
        'throttle' => round($throttle, 2),
        'brake' => round($brake, 2),
        'LapTime' => $lapTime,
        'LastLap' => $lastLap,
        'LapCount' => $lapCount,
        'BestLap' => $bestLap,
        'trackName' => $track
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    $time = date("H:i:s");
    echo "[$time] Snelheid: {$speed} km/u | RPM: {$rpm} | Versnelling: $gear\n";
    echo "         Verstuurd →  " . json_encode($data) . "\n";
    echo "         Antwoord ←  " . $result . "\n\n";

    usleep(500000); // elke 0.5 sec
}