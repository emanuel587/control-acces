<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lal1";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == 0) {
    $fileTmpPath = $_FILES['excelFile']['tmp_name'];
    $fileExtension = pathinfo($_FILES['excelFile']['name'], PATHINFO_EXTENSION);

    if (!in_array($fileExtension, ['xls', 'xlsx'])) {
        echo "<script>alert('Formato de archivo no válido. Por favor, suba un archivo Excel.'); window.history.back();</script>";
        exit;
    }

    try {
        $excel = new COM("Excel.Application");
        $excel->Visible = false;
        $excel->Workbooks->Open($fileTmpPath);

        $sheet = $excel->ActiveSheet;
        $totalRows = $sheet->UsedRange->Rows->Count;
        $batchSize = 100000;
        $last_num_empleado = null;
        $last_nombre = null;

        $conn->begin_transaction();

        for ($startRow = 1; $startRow <= $totalRows; $startRow += $batchSize) {
            $endRow = min($startRow + $batchSize - 1, $totalRows);

            for ($row = $startRow; $row <= $endRow; $row++) {
                $departamento = $sheet->Cells($row, 1)->Text;
                $num_empleado = $sheet->Cells($row, 2)->Text;
                $nombre = $sheet->Cells($row, 3)->Text;
                $fecha = $sheet->Cells($row, 4)->Text;
                $hora_llegada = $sheet->Cells($row, 5)->Text;
                $hora_salida = $sheet->Cells($row, 6)->Text;

                if (empty($num_empleado) && !is_null($last_num_empleado)) {
                    $num_empleado = $last_num_empleado;
                    $nombre = $last_nombre;
                } else {
                    $last_num_empleado = $num_empleado;
                    $last_nombre = $nombre;
                }

                if (!empty($num_empleado) && !empty($nombre)) {
                    $status_empleado = 'alta';
                    $stmtEmpleado = $conn->prepare("INSERT INTO empleados (num_empleado, departamento, nombre, status_empleado) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE departamento = VALUES(departamento), nombre = VALUES(nombre), status_empleado = VALUES(status_empleado)");
                    $stmtEmpleado->bind_param("ssss", $num_empleado, $departamento, $nombre, $status_empleado);
                    if (!$stmtEmpleado->execute()) {
                        throw new Exception("Error al insertar en la tabla empleados: " . $stmtEmpleado->error);
                    }
                    $stmtEmpleado->close();

                    $stmtCheckAsistencia = $conn->prepare("SELECT COUNT(*) FROM asistencia_empleados WHERE num_empleado = ? AND fecha_asistencia = ?");
                    $stmtCheckAsistencia->bind_param("ss", $num_empleado, $fecha);
                    $stmtCheckAsistencia->execute();
                    $stmtCheckAsistencia->bind_result($count);
                    $stmtCheckAsistencia->fetch();
                    $stmtCheckAsistencia->close();

                    if ($count == 0) {
                        // Determinar el estado de la asistencia
                        $asistencia = '';
                        if (!empty($hora_llegada) && !empty($hora_salida)) {
                            $asistencia = 'asistio';
                        } elseif (!empty($hora_llegada)) {
                            $asistencia = 'pendiente';
                        } else {
                            $asistencia = 'falta';
                        }

           
                        $stmtAsistencia = $conn->prepare("INSERT INTO asistencia_empleados (num_empleado, fecha_asistencia, hora_llegada, hora_salida, asistencia) VALUES (?, ?, ?, ?, ?)");
                        $stmtAsistencia->bind_param("sssss", $num_empleado, $fecha, $hora_llegada, $hora_salida, $asistencia);
                        if (!$stmtAsistencia->execute()) {
                            throw new Exception("Error al insertar en la tabla asistencia_empleados: " . $stmtAsistencia->error);
                        }
                        $stmtAsistencia->close();
                    } else {
                        echo "Fila $row omitida: registro de asistencia duplicado para el empleado $num_empleado en la fecha $fecha.";
                    }
                } else {
                    echo "Fila $row omitida: número de empleado o nombre no encontrado y no hay un último número de empleado válido disponible.";
                }
            }
        }
        $conn->commit();
        echo "<script>alert('Datos ingresados correctamente'); window.history.back();</script>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error procesando el archivo Excel: " . $e->getMessage();
    } finally {
        if ($excel) {
            $excel->Workbooks->Close();
            $excel->Quit();
        }
        $excel = null;
    }

} else {
    echo "<script>alert('Error al subir el archivo'); window.history.back();</script>";
}

$conn->close();
?>
