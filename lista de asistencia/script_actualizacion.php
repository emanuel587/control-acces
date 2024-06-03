







<?php

$local_host = 'localhost';
$local_username = 'root';
$local_password = '';
$local_dbname = 'lal1';

$local_conn = new mysqli($local_host, $local_username, $local_password, $local_dbname);

if ($local_conn->connect_error) {
    die("Error de conexión a la base de datos local: " . $local_conn->connect_error);
} else {
    echo "Conexión a la base de datos local exitosa.<br>";
}

$remote_host = 'localhost';
$remote_username = 'id22252179_root';
$remote_password = 'Accescontrol1.';
$remote_dbname = 'id22252179_lal1';

$remote_conn = new mysqli($remote_host, $remote_username, $remote_password, $remote_dbname);

if ($remote_conn->connect_error) {
    die("Error de conexión a la base de datos en la nube: " . $remote_conn->connect_error);
} else {
    echo "Conexión a la base de datos en la nube exitosa.<br>";
}
$local_query = "SELECT * FROM empleados";
$result = $local_conn->query($local_query);

if ($result->num_rows > 0) {
    
    while ($row = $result->fetch_assoc()) {
        $num_empleado = $row['num_empleado'];
        $departamento = $row['departamento'];
        $nombre = $row['nombre'];
        $status_empleado = $row['status_empleado'];


        $remote_query = "INSERT INTO empleados (num_empleado, departamento, nombre, status_empleado)
                         VALUES (?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE 
                             departamento = VALUES(departamento),
                             nombre = VALUES(nombre),
                             status_empleado = VALUES(status_empleado)";

        $stmt = $remote_conn->prepare($remote_query);
        $stmt->bind_param("ssss", $num_empleado, $departamento, $nombre, $status_empleado);
        
        if (!$stmt->execute()) {
            echo "Error al insertar/actualizar el registro del empleado con num_empleado $num_empleado: " . $stmt->error;
        }
    }
    echo "Datos transferidos exitosamente de la base de datos local a la base de datos en la nube.";
} else {
    echo "No se encontraron datos en la base de datos local.";
}

// Cerrar conexiones
$local_conn->close();
$remote_conn->close();
?>
