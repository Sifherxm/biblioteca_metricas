<?php
include "conexion.php";

$id = $_GET['id'];

$sql = "SELECT url_archivo FROM libros WHERE id_libro = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$archivo = $result['url_archivo'];
$ruta = "../pdf_libros/" . $archivo;

if (file_exists($ruta)) {
    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=\"$archivo\"");
    header("Content-Length: " . filesize($ruta));
    readfile($ruta);
    exit;
} else {
    echo "Archivo no encontrado.";
}
