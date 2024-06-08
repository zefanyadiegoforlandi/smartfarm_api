<?php
require_once '../config.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$conn = getDbConnection();

$id_sensor = $_GET['id_sensor'] ?? null;  
$method = $_SERVER['REQUEST_METHOD'];
$data = $_POST; 

switch ($method) {
    case 'GET':
        if ($id_sensor) {
            $result = getDataSensorByIdSensor($conn, $id_sensor);
        } else {
            $result = getAllDataSensors($conn);
        }
        break;
    case 'POST':
            $result = addDataSensor($conn, $data);
        break;
    case 'DELETE':
        if ($id_sensor) {
            $result = deleteDataSensor($conn, $id_sensor);
        } else {
            $result = "Please provide an ID to delete.";
        }
        break;
    default:
        $result = "Method not allowed.";
        break;
}

function getAllDataSensors($conn) {
    $sql = "SELECT * FROM data_sensor";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll();
}

function getDataSensorByIdSensor($conn, $id_sensor) {
    $sql = "SELECT * FROM data_sensor WHERE id_sensor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_sensor]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function addDataSensor($conn, $data) {
    $required_fields = ['intensitas_cahaya', 'kelembaban_tanah', 'kualitas_udara', 'RainDrop', 'kelembaban_udara', 'suhu', 'tekanan', 'ketinggian', 'waktu_perekaman'];
    
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            return "Field '$field' is required.";
        }
    }

    $sql = "INSERT INTO data_sensor (id_sensor, intensitas_cahaya, kelembaban_tanah, kualitas_udara, RainDrop, kelembaban_udara, suhu, tekanan, ketinggian, waktu_perekaman, created_at, updated_at) VALUES (:id_sensor, :intensitas_cahaya, :kelembaban_tanah, :kualitas_udara, :RainDrop, :kelembaban_udara, :suhu, :tekanan, :ketinggian, :waktu_perekaman, :created_at, :updated_at)";
    $stmt = $conn->prepare($sql);
    
    $stmt->execute([
        $data['id_sensor'],
        $data['intensitas_cahaya'],
        $data['kelembaban_tanah'],
        $data['kualitas_udara'],
        $data['RainDrop'],
        $data['kelembaban_udara'],
        $data['suhu'],
        $data['tekanan'],
        $data['ketinggian'],
        $data['waktu_perekaman']
    ]);
    
    return $stmt->rowCount() > 0 ? "Data sensor added successfully." : "Failed to add data sensor.";
}



function deleteDataSensor($conn, $id_sensor) {
    $sql = "DELETE FROM data_sensor WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    $stmt->execute([$id_sensor]);
    
    return $stmt->rowCount() > 0 ? "Data sensor deleted successfully." : "Failed to delete data sensor.";
}

header('Content-Type: application/json');
echo json_encode($result);
?>
