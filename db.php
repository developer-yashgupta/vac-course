<?php
$host = "localhost";
$username = "root";
$password = "kali";
$dbname = "vac_course";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper functions to make it work consistently
function db_query($sql, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_fetch($result) {
    return $result->fetch();
}

function db_num_rows($result) {
    return $result->rowCount();
}
?>