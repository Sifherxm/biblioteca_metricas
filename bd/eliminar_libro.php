<?php
session_start();
include "conexion.php";

/* PROTEGER ACCESO */
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.html");
    exit;
}

/* VALIDAR ID */
if (!isset($_GET['id'])) {
    header("Location: ../admin_ver_libros.php");
    exit;
}

$id = intval($_GET['id']);

/* OBTENER DATOS DEL LIBRO (para borrar archivos físicos) */
$sql = "SELECT imagen, url_archivo FROM libros WHERE id_libro = $id";
$resultado = $conn->query($sql);

if ($resultado->num_rows == 0) {
    header("Location: ../admin_ver_libros.php");
    exit;
}

$libro = $resultado->fetch_assoc();

/* ELIMINAR IMAGEN SI EXISTE */
if (!empty($libro['imagen']) && file_exists("../img_libros/" . $libro['imagen'])) {
    unlink("../img_libros/" . $libro['imagen']);
}

/* ELIMINAR PDF SI EXISTE */
if (!empty($libro['url_archivo']) && file_exists("../pdf_libros/" . $libro['url_archivo'])) {
    unlink("../pdf_libros/" . $libro['url_archivo']);
}

/* ELIMINAR DE LA BASE DE DATOS */
$conn->query("DELETE FROM libros WHERE id_libro = $id");

/* REDIRIGIR */
header("Location: admin_ver_libros.php");
exit;
?>