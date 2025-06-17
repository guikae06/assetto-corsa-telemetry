<?php

$HTTP_OK = 200;
$HTTP_CREATED = 201;

$connectToPostgres = function () {
    $dbHost = $_ENV['DB_HOST'];
    $dbPort = $_ENV['DB_PORT'];
    $dbName = $_ENV['DB_NAME'];
    $dbUser = $_ENV['DB_USER'];
    $dbPassword = $_ENV['DB_PASSWORD'];

    $dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}";
    return new PDO($dsn, $dbUser, $dbPassword);
};

function handlePostTelemetry($connectToDBFun)
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if ($data === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        return;
    }

    $pdo = $connectToDBFun();
    $query = "INSERT INTO telemetry (
                rpm, turbo, speed_kmh, gear, throttle, brake,
                lap_time_ms, last_lap_ms, lap_count, best_lap_ms, track_name
              ) VALUES (
                :rpm, :turbo, :speed_kmh, :gear, :throttle, :brake,
                :lap_time_ms, :last_lap_ms, :lap_count, :best_lap_ms, :track_name
              )";

    $statement = $pdo->prepare($query);

    $bindings = [
        ':rpm' => $data['rpm'] ?? null,
        ':turbo' => $data['turbo'] ?? null,
        ':speed_kmh' => $data['SpeedKMH'] ?? null,
        ':gear' => $data['gear'] ?? null,
        ':throttle' => $data['throttle'] ?? null,
        ':brake' => $data['brake'] ?? null,
        ':lap_time_ms' => $data['LapTime'] ?? null,
        ':last_lap_ms' => $data['LastLap'] ?? null,
        ':lap_count' => $data['LapCount'] ?? null,
        ':best_lap_ms' => $data['BestLap'] ?? null,
        ':track_name' => $data['trackName'] ?? null
    ];

    try {
        $pdo->beginTransaction();
        $statement->execute($bindings);
        $pdo->commit();
        http_response_code($HTTP_CREATED);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'Database insert failed', 'details' => $e->getMessage()]);
    }
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePostTelemetry($connectToPostgres);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
