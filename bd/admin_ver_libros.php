<?php
session_start();
include "conexion.php";

/* PROTEGER ACCESO */
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.html");
    exit;
}

/* CONSULTA LIBROS (sin filtro SQL, porque el filtro será dinámico en JS) */
$sql = "SELECT libros.*, categorias.nombre AS categoria 
        FROM libros 
        LEFT JOIN categorias 
        ON libros.id_categoria = categorias.id_categoria
        ORDER BY libros.id_libro DESC";

$resultado = $conn->query($sql);
?>

<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Todos los Libros</title>

<link rel="stylesheet" href="../principal.css">
<link rel="stylesheet" href="../admin_libros.css">

</head>

<body>
<div class="container">

<!-- ================= SIDEBAR ================= -->
<aside class="sidebar">
    <div class="logo">
        <img src="../img/logo_biblioteca.png" alt="Logo">
    </div>

    <button onclick="window.location.href='admin_agregar_libros.php'">
        Agregar Libro
    </button>
</aside>

<!-- ================= MAIN ================= -->
<main class="main">

<!-- TOPBAR -->
<header class="topbar">
    <h1>Todos los Libros</h1>

    <div class="user-menu">
        <div class="user-trigger">
            <img src="../img/alumno_icono.png" alt="Usuario">
            <span class="user-name">
                <?php echo htmlspecialchars($_SESSION['nombre']); ?>
            </span>
        </div>

        <div class="dropdown">
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </div>
</header>

<!-- CONTENIDO -->
<section class="content">

<div class="tabla-libros">

    <!-- 🔎 BUSCADOR EN TIEMPO REAL (ARRIBA DE LA TABLA) -->
    <input
        type="text"
        id="buscadorLibros"
        placeholder="Buscar por título, autor, categoría, año o tipo..."
        style="width:100%; padding:10px; border-radius:10px; border:1px solid #ccc; margin-bottom:15px;"
    >

<table id="tablaLibros">
<thead>
<tr>
    <th>Imagen</th>
    <th>Título</th>
    <th>Autor</th>
    <th>Categoría</th>
    <th>Año</th>
    <th>Tipo</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php if($resultado && $resultado->num_rows > 0): ?>

<?php while($libro = $resultado->fetch_assoc()): ?>

<tr>

<td>
<?php if(!empty($libro['imagen'])): ?>
    <img src="../img_libros/<?php echo htmlspecialchars($libro['imagen']); ?>" alt="Libro">
<?php else: ?>
    Sin imagen
<?php endif; ?>
</td>

<td><?php echo htmlspecialchars($libro['titulo']); ?></td>
<td><?php echo htmlspecialchars($libro['autor']); ?></td>
<td><?php echo htmlspecialchars($libro['categoria']); ?></td>
<td><?php echo htmlspecialchars($libro['anio_publicacion']); ?></td>

<td>
<?php if($libro['tipo'] == "digital"): ?>
    <span class="estado-digital">Digital</span>
<?php else: ?>
    <span class="estado-fisico">Físico</span>
<?php endif; ?>
</td>

<td>
    <a href="admin_editar_libro.php?id=<?php echo (int)$libro['id_libro']; ?>" 
       class="btn-editar">
       Editar
    </a>

    <a href="eliminar_libro.php?id=<?php echo (int)$libro['id_libro']; ?>" 
       class="btn-eliminar"
       onclick="return confirm('¿Eliminar este libro?')">
       Eliminar
    </a>
</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr class="fila-vacia">
<td colspan="7">No hay libros registrados.</td>
</tr>

<?php endif; ?>

</tbody>
</table>

<!-- Mensaje cuando el filtro no encuentra nada -->
<p id="msgSinResultados" style="display:none; margin-top:10px;">
    No se encontraron resultados.
</p>

</div>

</section>
</main>
</div>

<!-- DROPDOWN USUARIO -->
<script>
document.querySelector(".user-trigger").addEventListener("click", function(){
    document.querySelector(".dropdown").classList.toggle("show");
});
</script>

<!-- 🔥 FILTRO EN TIEMPO REAL -->
<script>
const input = document.getElementById("buscadorLibros");
const tabla = document.getElementById("tablaLibros");
const tbody = tabla.querySelector("tbody");
const msg = document.getElementById("msgSinResultados");

input.addEventListener("input", function () {
    const q = this.value.toLowerCase().trim();
    let visibles = 0;

    const filas = Array.from(tbody.querySelectorAll("tr"));

    filas.forEach(tr => {
        // si es la fila "No hay libros registrados", no la uses para filtrar
        if (tr.classList.contains("fila-vacia")) return;

        const texto = tr.innerText.toLowerCase();
        const mostrar = texto.includes(q);

        tr.style.display = mostrar ? "" : "none";
        if (mostrar) visibles++;
    });

    // si hay libros pero el filtro deja 0, mostramos mensaje
    // si no hay libros (fila-vacia), ocultamos mensaje
    const hayFilaVacia = tbody.querySelector(".fila-vacia");
    if (!hayFilaVacia) {
        msg.style.display = (visibles === 0 && q !== "") ? "block" : "none";
    } else {
        msg.style.display = "none";
    }
});
</script>

</body>
</html>