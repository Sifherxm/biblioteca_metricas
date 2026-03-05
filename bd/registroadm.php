<?php
include "conexion.php";

$nombre = $_POST['nombre'];
$ap_paterno = $_POST['apellido_paterno'];
$ap_materno = $_POST['apellido_materno'];
$matricula = $_POST['matricula'];
$correo = $_POST['correo'];
$password = $_POST['password'];

$rol = "admin"; //  Rol correcto

$password_hash = password_hash($password, PASSWORD_DEFAULT);

/*  Verificar matrícula */
$sql = "SELECT id FROM usuarios WHERE matricula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
header("Location: /biblioteca/registroadm.html?error=matricula");
    exit;
}

/*  Verificar correo */
$sql = "SELECT id FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
header("Location: /biblioteca/registroadm.html?error=correo");
    exit;
}

/* Insertar ADMIN */
/* Como division y grupo son NOT NULL,
   enviamos valores por defecto */
$division = "ADMIN";
$grupo = "ADMIN";

$sql = "INSERT INTO usuarios 
(nombre, apellido_paterno, apellido_materno, matricula, division, grupo, correo, password, rol)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssss",
    $nombre,
    $ap_paterno,
    $ap_materno,
    $matricula,
    $division,
    $grupo,
    $correo,
    $password_hash,
    $rol
);

if ($stmt->execute()) {
    header("Location: /biblioteca/login.html");
    exit;
} else {
    echo "Error al registrar: " . $stmt->error;
}
?>