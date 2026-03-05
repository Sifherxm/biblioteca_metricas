<?php
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.html");
    exit;
}

include "conexion.php";

$matricula = $_POST['matricula'];
$password  = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE matricula = ?"; // 🔥 SOLO cambiamos alumnos → usuarios
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error en la consulta");
}

$stmt->bind_param("s", $matricula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $usuario = $result->fetch_assoc();

    if (password_verify($password, $usuario['password'])) {

        session_start();
        $_SESSION['id_usuario'] = $usuario['id'];
        $_SESSION['matricula']  = $usuario['matricula'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['rol']        = $usuario['rol']; // agregamos esto

        //  SOLO agregamos esto:
        if ($usuario['rol'] === "admin") {
            header("Location: admin_ver_libros.php");
        } else {
            header("Location: ../principal.php");
        }

        exit;

    } else {

        header("Location: ../login.html?error=pass");
        exit;
    }

} else {

    header("Location: ../login.html?error=user");
    exit;
}

$stmt->close();
$conn->close();
?>