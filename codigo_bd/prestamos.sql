CREATE TABLE prestamos (
    id_prestamo INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    id_inventario INT,
    fecha_prestamo DATE,
    fecha_devolucion DATE,
    fecha_entrega DATE,
    estado ENUM('activo','devuelto','vencido') DEFAULT 'activo',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_inventario) REFERENCES inventario(id_inventario)
);  