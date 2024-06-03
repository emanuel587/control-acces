<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asistencia</title>
</head>
<body>
    <h1>Reporte de Asistencia</h1>
    <form method="post" action="">
        <label for="fecha_inicio">Fecha de Inicio:</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" required>
        <label for="fecha_fin">Fecha de Fin:</label>
        <input type="date" id="fecha_fin" name="fecha_fin" required>
        <label for="departamento">Departamento:</label>
        <select id="departamento" name="departamento">
            <option value="">Todos</option>
            <?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lal1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT DISTINCT departamento FROM empleados";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['departamento'] . '">' . $row['departamento'] . '</option>';
    }
}

$conn->close();
?>
</select>
<input type="submit" value="Generar Reporte">
</form>
<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "lal1";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $departamento = $_POST['departamento'];

    $sql = "
        SELECT e.num_empleado, e.nombre, e.departamento, a.fecha_asistencia, a.asistencia
        FROM empleados e
        LEFT JOIN asistencia_empleados a ON e.num_empleado = a.num_empleado AND a.fecha_asistencia BETWEEN ? AND ?";

    if (!empty($departamento)) {
        $sql .= " WHERE e.departamento = ?";
    }

    $sql .= " ORDER BY e.num_empleado, a.fecha_asistencia";

    $stmt = $conn->prepare($sql);

    if (!empty($departamento)) {
        $stmt->bind_param("sss", $fecha_inicio, $fecha_fin, $departamento);
    } else {
        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $empleados = [];
    while ($row = $result->fetch_assoc()) {
        $empleados[$row['num_empleado']]['nombre'] = $row['nombre'];
        $empleados[$row['num_empleado']]['departamento'] = $row['departamento'];
        $empleados[$row['num_empleado']]['asistencia'][$row['fecha_asistencia']] = [
            'asistencia' => $row['asistencia']
        ];
    }
    $stmt->close();
    $conn->close();

    echo '<table border="1">';
    echo '<tr>';
    echo '<th>Número de Empleado</th>';
    echo '<th>Nombre</th>';
    echo '<th>Departamento</th>';

    $dias_semana = ['D', 'L', 'M', 'M', 'J', 'V', 'S'];
    $currentDate = strtotime($fecha_inicio);
    $endDate = strtotime($fecha_fin);
    $daysCount = 0;
    while ($currentDate <= $endDate && $daysCount < 15) {
        $dia_semana = $dias_semana[date('w', $currentDate)];
        echo '<th>' . date('d-m-Y', $currentDate) . '<br>' . $dia_semana . '</th>';
        $currentDate = strtotime("+1 day", $currentDate);
        $daysCount++;
    }
    echo '</tr>';

    foreach ($empleados as $num_empleado => $info) {
        echo '<tr>';
        echo '<td>' . $num_empleado . '</td>';
        echo '<td>' . $info['nombre'] . '</td>';
        echo '<td>' . $info['departamento'] . '</td>';

        $currentDate = strtotime($fecha_inicio);
        $daysCount = 0;
        while ($currentDate <= $endDate && $daysCount < 15) {
            $fecha = date('Y-m-d', $currentDate);
            $asistencia = isset($info['asistencia'][$fecha]) ? $info['asistencia'][$fecha]['asistencia'] : 'No llegada';

            echo '<td>' . $asistencia . '</td>';

            $currentDate = strtotime("+1 day", $currentDate);
            $daysCount++;
        }

        echo '</tr>';
    }
    echo '</table>';
}
?>
