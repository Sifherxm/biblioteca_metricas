<?php
include "conexion.php";

$sql = "SELECT id_libro, titulo, imagen
        FROM libros
        WHERE imagen IS NOT NULL
        ORDER BY RAND()
        LIMIT 5";

$result = $conn->query($sql);

$libros = [];

while ($row = $result->fetch_assoc()) {
    $libros[] = $row;
}

echo json_encode($libros);
