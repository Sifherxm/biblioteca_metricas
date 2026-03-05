<?php
include "conexion.php";

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$limit = isset($_GET["limit"]) ? (int)$_GET["limit"] : 5;

if ($id <= 0) {
  header("Content-Type: application/json; charset=utf-8");
  echo json_encode([]);
  exit;
}

if ($limit < 1) $limit = 5;
if ($limit > 12) $limit = 12;

/* Solo libros con imagen */
$sql = "SELECT id_libro, titulo, imagen
        FROM libros
        WHERE id_categoria = $id
          AND imagen IS NOT NULL AND imagen <> ''
        ORDER BY RAND()
        LIMIT $limit";

$res = $conn->query($sql);

$libros = [];
while ($row = $res->fetch_assoc()) {
  $libros[] = $row;
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($libros);