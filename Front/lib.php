<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("pgsql:host=db;port=5432;dbname=telemetry", "guikae06", "ac_project");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $method = $_SERVER['REQUEST_METHOD'];
    $id = $_GET['id'] ?? null;

    // Lees JSON-body voor POST & PUT
    $body = file_get_contents("php://input");
    $data = json_decode($body, true);

    //  READ (GET)
    if ($method === 'GET') {
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM telemetry WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                http_response_code(404);
                echo json_encode(['error' => 'Record niet gevonden']);
            } else {
                echo json_encode($row);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM telemetry ORDER BY id DESC LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($row ?: ['error' => 'Nog geen data beschikbaar']);
        }
        exit;
    }

    // CREATE (POST)
    if ($method === 'POST') {
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Ongeldige JSON']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO telemetry (
                rpm, turbo, speed_kmh, gear,
                throttle, brake,
                lap_time_ms, last_lap_ms, lap_count, best_lap_ms,
                track_name
            ) VALUES (
                :rpm, :turbo, :speed_kmh, :gear,
                :throttle, :brake,
                :lap_time_ms, :last_lap_ms, :lap_count, :best_lap_ms,
                :track_name
            )
        ");

        $stmt->execute([
            ':rpm'         => $data['rpm'] ?? null,
            ':turbo'       => $data['turbo'] ?? null,
            ':speed_kmh'   => $data['SpeedKMH'] ?? null,
            ':gear'        => $data['gear'] ?? null,
            ':throttle'    => $data['throttle'] ?? null,
            ':brake'       => $data['brake'] ?? null,
            ':lap_time_ms' => $data['LapTime'] ?? null,
            ':last_lap_ms' => $data['LastLap'] ?? null,
            ':lap_count'   => $data['LapCount'] ?? null,
            ':best_lap_ms' => $data['BestLap'] ?? null,
            ':track_name'  => $data['trackName'] ?? null
        ]);

        echo json_encode(['status' => 'Record toegevoegd']);
        exit;
    }

    // UPDATE (PUT)
    if ($method === 'PUT') {
        if (!$id || !$data) {
            http_response_code(400);
            echo json_encode(['error' => 'id of data ontbreekt']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE telemetry SET
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
            WHERE id = :id
        ");

        $stmt->execute([
            ':rpm'         => $data['rpm'] ?? null,
            ':turbo'       => $data['turbo'] ?? null,
            ':speed_kmh'   => $data['SpeedKMH'] ?? null,
            ':gear'        => $data['gear'] ?? null,
            ':throttle'    => $data['throttle'] ?? null,
            ':brake'       => $data['brake'] ?? null,
            ':lap_time_ms' => $data['LapTime'] ?? null,
            ':last_lap_ms' => $data['LastLap'] ?? null,
            ':lap_count'   => $data['LapCount'] ?? null,
            ':best_lap_ms' => $data['BestLap'] ?? null,
            ':track_name'  => $data['trackName'] ?? null,
            ':id'          => $id
        ]);

        echo json_encode(['status' => 'Record bijgewerkt']);
        exit;
    }

    //  DELETE
    if ($method === 'DELETE') {
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'id ontbreekt']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM telemetry WHERE id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode(['status' => 'Record verwijderd']);
        exit;
    }

    // ❌ Ongeldige methode
    http_response_code(405);
    echo json_encode(['error' => 'Methode niet toegestaan']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'details' => $e->getMessage()
    ]);
}