<?php
include "conexion.php";

$nombre = $_POST['nombre'];
$ap_paterno = $_POST['apellido_paterno'];
$ap_materno = $_POST['apellido_materno'];
$matricula = $_POST['matricula'];
$division = $_POST['division'];
$grupo = $_POST['grupo'];
$correo = $_POST['correo'];
$password = $_POST['password'];

$rol = "estudiante"; //  IMPORTANTE

$password_hash = password_hash($password, PASSWORD_DEFAULT);

/*  Verificar matrícula */
$sql = "SELECT id FROM usuarios WHERE matricula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $matricula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: ../registroal.html?error=matricula");
    exit;
}

/*  Verificar correo */
$sql = "SELECT id FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: ../registroal.html?error=correo");
    exit;
}

/*  Insertar alumno */
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
    header("Location: ../login.html");
    exit;
}
?>