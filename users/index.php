<?php
require_once '../config.php';
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


$conn = getDbConnection();
$data = $_POST;

$id = $_GET['id'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if ($id) {
            $result = getUserById($conn, $id);
        } else {
            $result = getAllUsers($conn);
        }
        break;
    case 'POST':
        if ($id) {
            $result = updateUser($conn, $id, $data);
        } else {
            $result = addUser($conn, $data);
        }
        break;
    case 'DELETE':
        if ($id) {
            $result = deleteUser($conn, $id);
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

function getAllUsers($conn) {
    $sql = "SELECT * FROM users";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserById($conn, $id) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function addUser($conn, $data) {
    $required_fields = ['name', 'email', 'password', 'level', 'alamat_user'];

    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            return "Required field '$field' is missing.";
        }
    }

    $sql = "INSERT INTO users (name, email, password, level, alamat_user, created_at, updated_at) VALUES (:name, :email, :password, :level, :alamat_user, :created_at, :updated_at)";
    $stmt = $conn->prepare($sql);

    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');

    try {
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'level' => $data['level'],
            'alamat_user' => $data['alamat_user'],
            'created_at' => $created_at,
            'updated_at' => $updated_at
        ]);
        return $stmt->rowCount() > 0 ? "User added successfully." : "Failed to add user.";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}


function updateUser($conn, $id, $data) {
    if (empty($data)) {
        return "No data provided for update.";
    }

    $allowed_fields = ['name', 'email', 'password', 'level', 'alamat_user'];
    $update_fields = [];

    foreach ($data as $key => $value) {
        if (in_array($key, $allowed_fields)) {
            $update_fields[] = "$key = :$key";
        }
    }

    if (empty($update_fields)) {
        return "No valid fields provided for update.";
    }

    $update_fields[] = "updated_at = :updated_at";
    $update_fields_str = implode(", ", $update_fields);

    $sql = "UPDATE users SET $update_fields_str WHERE id = :id";
    $stmt = $conn->prepare($sql);

    $data['updated_at'] = date('Y-m-d H:i:s');
    $data['id'] = $id;

    try {
        $stmt->execute($data);
        return $stmt->rowCount() > 0 ? "User updated successfully." : "No changes made to the user.";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}


function deleteUser($conn, $id) {
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    try {
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0 ? "User deleted successfully." : "Failed to delete user.";
    } catch (PDOException $e) {
        return "Error: " . $e->getMessage();
    }
}



