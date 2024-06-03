<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    echo "<pre>";
    print_r($_POST); 
    echo "</pre>";

    
    $Num_empleado = isset($_POST["Num_empleado"]) ? $_POST["Num_empleado"] : null;
    $Nombre = isset($_POST["Nombre"]) ? $_POST["Nombre"] : null;
    $Puesto = isset($_POST["Puesto"]) ? $_POST["Puesto"] : null;
    $RFC = isset($_POST["RFC"]) ? $_POST["RFC"] : null;
    $Nombre_Departamento = isset($_POST["Nombre_Departamento"]) ? $_POST["Nombre_Departamento"] : null;
    $Fecha_ingreso_contrato = isset($_POST["Fecha_ingreso_contrato"]) ? $_POST["Fecha_ingreso_contrato"] : null;
    $Fecha_vencimiento_contrato = isset($_POST["Fecha_vencimiento_contrato"]) ? $_POST["Fecha_vencimiento_contrato"] : null;

    // Prepare SQL statement
    $sql = "INSERT INTO Empleado (Num_empleado, Nombre, Puesto, RFC, Nombre_Departamento, Fecha_ingreso_contrato, Fecha_vencimiento_contrato) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Error al preparar la consulta: " . $conn->error);
    }

    $stmt->bind_param("issssss", $Num_empleado, $Nombre, $Puesto, $RFC, $Nombre_Departamento, $Fecha_ingreso_contrato, $Fecha_vencimiento_contrato);


    

    if ($stmt->execute()) {
        echo "<script>alert('Empleado agregado con éxito.');</script>";
    } else {
        echo "<script>alert('Error al agregar empleado: " . $stmt->error . "');</script>";
    }


    $stmt->close();
}

$conn->close();
?>
?