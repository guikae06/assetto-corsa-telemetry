<?php
header('Content-Type: application/json; charset=utf-8');

$HTTP_OK = 200;
$HTTP_CREATED = 201;

function connectToPostgres() {
    $dbHost = $_ENV['DB_HOST'] ?? 'localhost';
    $dbPort = $_ENV['DB_PORT'] ?? '5432';
    $dbName = $_ENV['DB_NAME'] ?? 'webtech';
    $dbUser = $_ENV['DB_USER'] ?? 'webtechuser';
    $dbPassword = $_ENV['DB_PASSWORD'] ?? 'password';

    $dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}";
    return new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
}

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

try {
    $pdo = connectToPostgres();

    if ($method === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if ($data === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON']);
            exit;
        }

        $query = "INSERT INTO telemetry (
            rpm, turbo, speed_kmh, gear, throttle, brake,
            lap_time_ms, last_lap_ms, lap_count, best_lap_ms, track_name
        ) VALUES (
            :rpm, :turbo, :speed_kmh, :gear, :throttle, :brake,
            :lap_time_ms, :last_lap_ms, :lap_count, :best_lap_ms, :track_name
        ) RETURNING id";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
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
        ]);

        http_response_code($HTTP_CREATED);
        echo json_encode(['status' => 'created']);
        exit;

    } elseif ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM telemetry WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $stmt = $pdo->query("SELECT * FROM telemetry ORDER BY datetime DESC LIMIT 50");
        }

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code($HTTP_OK);
        echo json_encode($result);
        exit;

    } elseif ($method === 'PUT' || $method === 'PATCH') {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for update']);
            exit;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        $query = "UPDATE telemetry SET
            rpm = :rpm,
            turbo = :turbo,
            speed_kmh = :speed_kmh,
            gear = :gear,
            throttle = :throttle,
            brake = :brake,
            lap_time_ms = :lap_time_ms,
            last_lap_ms = :last_lap_ms,
            lap_count = :lap_count,
            best_lap_ms = :best_lap_ms,
            track_name = :track_name
            WHERE id = :id RETURNING *";

        $stmt = $pdo->prepare($query);
        $stmt->execute([
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
            ':track_name' => $data['trackName'] ?? null,
            ':id' => $id
        ]);

        echo json_encode(['status' => 'updated']);
        exit;

    } elseif ($method === 'DELETE') {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID is required for deletion']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM telemetry WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'deleted']);
        exit;

    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}
