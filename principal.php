<?php
session_start();
include "bd/conexion.php";

/* PROTECCIÓN */
if (!isset($_SESSION['id_usuario'])) {
  header("Location: login.html");
  exit;
}

/* Traer categorías */
$cats = $conn->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC");
$categorias = [];
while($c = $cats->fetch_assoc()){
  $categorias[] = $c;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Biblioteca Online</title>
  <link rel="stylesheet" href="principal.css?v=1000" />
</head>

<body>
<div class="container">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="logo">
      <img src="img/logo_biblioteca.png" alt="Logo Biblioteca">
    </div>

    <button>Gestión Inventario</button><br>
    <h4 id="catego">CATEGORÍAS</h4>
    <a href="#recomendaciones"><button>Recomendaciones</button></a>
    <button>Ciencia</button>
    <button>Drama</button>
    <button>Romance</button>
    <button>Tecnología</button>
    <button>Terror</button>
  </aside>

  <!-- MAIN -->
  <main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
      <h1>Biblioteca online</h1>

      <div class="user-icons">
        <img src="img/configuracion.png" alt="Configuración">
        <img src="img/notificación.png" alt="Notificaciones">

        <div class="user-menu">
          <div class="user-trigger" id="userTrigger">
            <img src="img/alumno_icono.png" alt="Usuario">
            <span class="user-name">
              <?php echo htmlspecialchars($_SESSION['nombre']); ?>
            </span>
          </div>

          <div class="dropdown" id="dropdownMenu">
            <a href="bd/logout.php">Cerrar sesión</a>
          </div>
        </div>
      </div>
    </header>

    <!-- BUSCADOR -->
    <div class="search">
      <input type="text" id="buscador" placeholder="Búsqueda de libros, artículos o autores...">
    </div>

    <!-- ACCIONES -->
    <div class="actions">
    </div>

    <!-- Resultados buscador -->
    <div id="resultados-busqueda" class="resultados"></div>

    <!-- CONTENIDO -->
    <section class="content">

      <!-- Carrusel general (opcional) -->
      <div class="catalogo">
        <section id="recomendaciones">
        <h3>Recomendaciones</h3>
        </section>

        <div class="carousel-container">
          <button class="carousel-btn left" type="button" onclick="cargarRandom('reco')">‹</button>

          <div class="carousel">
            <div class="carousel-track">
              <div class="carousel-group" id="carousel-reco"></div>
            </div>
          </div>

          <button class="carousel-btn right" type="button" onclick="cargarRandom('reco')">›</button>
        </div>
      </div>

      <!-- Carruseles por categoría -->
      <?php foreach($categorias as $cat): ?>
        <div class="catalogo">
          <h3><?php echo htmlspecialchars($cat['nombre']); ?></h3>

          <div class="carousel-container">
            <button class="carousel-btn left" type="button"
              onclick="cargarCategoria(<?php echo (int)$cat['id_categoria']; ?>)">
              ‹
            </button>

            <div class="carousel">
              <div class="carousel-track">
                <div class="carousel-group"
                     id="carousel-cat-<?php echo (int)$cat['id_categoria']; ?>"
                     data-cat="<?php echo (int)$cat['id_categoria']; ?>">
                </div>
              </div>
            </div>

            <button class="carousel-btn right" type="button"
              onclick="cargarCategoria(<?php echo (int)$cat['id_categoria']; ?>)">
              ›
            </button>
          </div>
        </div>
      <?php endforeach; ?>

    </section>
  </main>
</div>

<script>
/* Dropdown */
const userTrigger = document.getElementById("userTrigger");
const dropdownMenu = document.getElementById("dropdownMenu");
userTrigger.addEventListener("click", () => dropdownMenu.classList.toggle("show"));

/* Render cards (reutilizable) */
function renderLibros(containerId, data) {
  const container = document.getElementById(containerId);
  container.innerHTML = "";

  if (!data || data.length === 0) {
    container.innerHTML = `<div style="padding:20px;">No hay libros en esta categoría.</div>`;
    return;
  }

  data.forEach(libro => {
    container.innerHTML += `
      <a href="bd/libro.php?id=${libro.id_libro}" class="card">
        <img loading="lazy" src="/biblioteca/img_libros/${libro.imagen}" alt="${libro.titulo}">
        <p>${libro.titulo}</p>
      </a>
    `;
  });
}

/* Recomendaciones (random general) */
function cargarRandom() {
  fetch("bd/libros_random.php")  // recuerda que ahora debe ser LIMIT 5
    .then(res => res.json())
    .then(data => renderLibros("carousel-reco", data))
    .catch(err => console.error(err));
}

/* Carrusel por categoría */
function cargarCategoria(idCategoria) {
  fetch(`bd/libros_por_categoria.php?id=${idCategoria}&limit=5`)
    .then(res => res.json())
    .then(data => renderLibros(`carousel-cat-${idCategoria}`, data))
    .catch(err => console.error(err));
}

/* Cargar todo al inicio */
cargarRandom();

document.querySelectorAll(".carousel-group[data-cat]").forEach(div => {
  const idCat = div.getAttribute("data-cat");
  cargarCategoria(idCat);
});

/* ===== BUSCADOR (igual que el tuyo) ===== */
const buscador = document.getElementById("buscador");
const resultados = document.getElementById("resultados-busqueda");

buscador.addEventListener("keyup", function() {
  let texto = buscador.value.trim();

  if(texto.length < 2){
    resultados.innerHTML = "";
    resultados.style.display = "none";
    return;
  }

  fetch("bd/buscarlibros.php?q=" + encodeURIComponent(texto))
    .then(res => res.json())
    .then(data => {
      resultados.innerHTML = "";

      if(data.length === 0){
        resultados.innerHTML = "<p class='no-results'>No se encontraron resultados</p>";
        resultados.style.display = "block";
        return;
      }

      resultados.style.display = "grid";

      data.forEach(libro => {
        resultados.innerHTML += `
          <a href="bd/libro.php?id=${libro.id_libro}" class="result-card">
            <img loading="lazy" src="/biblioteca/img_libros/${libro.imagen}" alt="${libro.titulo}">
            <div>
              <p class="result-title">${libro.titulo}</p>
              <small class="result-author">${libro.autor ?? ""}</small>
            </div>
          </a>
        `;
      });
    })
    .catch(err => console.error(err));
});
</script>
</body>
</html>