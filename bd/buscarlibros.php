<?php
include "conexion.php"; 

if(isset($_GET['q'])) {

    $busqueda = mysqli_real_escape_string($conn, $_GET['q']);

    $sql = "SELECT * FROM libros 
            WHERE titulo LIKE '%$busqueda%' 
            OR autor LIKE '%$busqueda%'";

    $resultado = mysqli_query($conn, $sql);

    $libros = [];

    while($fila = mysqli_fetch_assoc($resultado)){
        $libros[] = $fila;
    }

    echo json_encode($libros);
}
?>