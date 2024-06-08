<?php
require_once '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$conn = getDbConnection();
$data = $_POST;

$id_sensor = $_GET['id_sensor'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if ($id_sensor) {
            $result = getSensorById($conn, $id_sensor);
        } else {
            $result = getAllSensors($conn);
        }
        break;
    case 'POST':
        if ($id_sensor) {
            $result = updateSensor($conn, $id_sensor, $data);
        } else {
            $result = addSensor($conn, $data);
        }
        break;
    case 'DELETE':
        if ($id_sensor) {
            $result = deleteSensor($conn, $id_sensor);
        } else {
            $result = "Please provide an ID to delete.";
        }
        break;
    default:
        $result = "Method not allowed.";
        break;
}

header('Content-Type: application/json');
echo json_encode($result);

function getAllSensors($conn) {
    $sql = "SELECT * FROM sensor";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSensorById($conn, $id_sensor) {
    $sql = "SELECT * FROM sensor WHERE id_sensor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_sensor]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addSensor($conn, $data) {
    $required_fields = ['id_sensor','nama_sensor', 'tanggal_aktivasi', 'id_lahan'];

    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            return ["status" => "error", "message" => "Field '$field' is required."];
        }
    }

    $sql = "INSERT INTO sensor (id_sensor, nama_sensor, id_lahan, tanggal_aktivasi) VALUES (:id_sensor, :nama_sensor, :id_lahan, :tanggal_aktivasi)";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([
            ':id_sensor' => $data['id_sensor'],
            ':id_lahan' => $data['id_lahan'],
            ':tanggal_aktivasi' => $data['tanggal_aktivasi'],
            'nama_sensor' => $data['nama_sensor']
        ]);
        return $stmt->rowCount() > 0 ? ["status" => "success", "message" => "Sensor added successfully."] : ["status" => "error", "message" => "Failed to add sensor."];
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Error: " . $e->getMessage()];
    }
}

function updateSensor($conn, $id_sensor, $data) {
    if (empty($data)) {
        return ["status" => "error", "message" => "No data provided for update."];
    }

    $allowed_fields = ['id_lahan', 'nama_sensor', 'tanggal_aktivasi'];
    $update_fields = [];

    foreach ($data as $key => $value) {
        if (in_array($key, $allowed_fields)) {
            $update_fields[] = "$key = :$key";
        }
    }

    if (empty($update_fields)) {
        return ["status" => "error", "message" => "No valid fields provided for update."];
    }

    $update_fields_str = implode(", ", $update_fields);
    $sql = "UPDATE sensor SET $update_fields_str WHERE id_sensor = :id_sensor";
    $stmt = $conn->prepare($sql);

    $data['id_sensor'] = $id_sensor;

    try {
        $stmt->execute($data);
        return $stmt->rowCount() > 0 ? ["status" => "success", "message" => "Sensor updated successfully."] : ["status" => "error", "message" => "No changes made to the sensor."];
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Error: " . $e->getMessage()];
    }
}

function deleteSensor($conn, $id_sensor) {
    $sql = "DELETE FROM sensor WHERE id_sensor = ?";
    $stmt = $conn->prepare($sql);
    try {
        $stmt->execute([$id_sensor]);
        return $stmt->rowCount() > 0 ? ["status" => "success", "message" => "Sensor deleted successfully."] : ["status" => "error", "message" => "Failed to delete sensor."];
    } catch (PDOException $e) {
        return ["status" => "error", "message" => "Error: " . $e->getMessage()];
    }
}
?>
