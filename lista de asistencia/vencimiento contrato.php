<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lal";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit'])) {
        $Num_empleado = $_POST['Num_empleado'];
        $Nombre = $_POST['Nombre'];
        $Puesto = $_POST['Puesto'];
        $Fecha_ingreso_contrato = $_POST['Fecha_ingreso_contrato'];
        $Fecha_vencimiento_contrato = $_POST['Fecha_vencimiento_contrato'];

        $sql = "UPDATEe Empleados SET Nombre=?, Puesto=?, Fecha_ingreso_contrato=?, Fecha_vencimiento_contrato=? WHERE Num_empleado=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $Nombre, $Puesto, $Fecha_ingreso_contrato, $Fecha_vencimiento_contrato, $Num_empleado);
        $stmt->execute();
        $stmt->close();
    }
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT Num_empleado, Nombre, departamento FROM Empleados WHERE Nombre LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = "%" . $search . "%";
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();

$employees = [];
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
    <link rel="stylesheet" href="styles/menu1.css">
<head>
    <title>vencimiento de contratos</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 10px;
           
        }
        th, td {
            text-align: center;
        }
    
    </style>


<header class="header">
        <div class="container">
            <div class="btn-menu">
                
                <label for="btn-menu">☰ Menu</label>
                
            </div>
            <div class="logos">
               
            </div>
            <nav class="menu">
                <img src="img/logo-los-angeles-loco.png" alt="Mi Imagen" class="mi-imagen">
                
            </nav>
        </div>
    </header>
    <div class="capa"></div>

    

    <input type="checkbox" id="btn-menu">
    <div class="container-menu">
        <div class="cont-menu">
            <nav>
                <h4>Menu</h4>
                <a href="index.html">
                    <i class="fa-solid fa-pen-to-square"></i> Cargar excel
                </a>
                <a href="permisos.html">
                    <i class="fa-solid fa-folder"></i>Pantalla de permisos
                </a>
                <a href="vencimiento contrato.php">
                    <i class="fa-regular fa-clipboard"></i> Vencimiento de contratos
                </a>
                <a href="agregar_empleado.html">
                    <i class="fa-regular fa-clipboard"></i> Agregar empleado
                </a>
            </nav>
            <label for="btn-menu">✖️</label>
        </div>
    </div>
</head>
<body>
    <h2>Edtitar empleado</h2>
    <form method="GET" action="">
        <label for="search">busca el nombre del empleado</label>
        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <input type="submit" value="Buscar">
    </form>
    <br>
    <table>
        <tr>
            <th>Num Empleado</th>
            <th>Nombre</th>
            <th>Puesto</th>
           
            <th>Actualizar</th>
        </tr>
        <?php foreach ($employees as $employee): ?>
            <tr>
                <form method="POST" action="">
                    <td><input type="text" name="Num_empleado" value="<?php echo $employee['Num_empleado']; ?>" readonly></td>
                    <td><input type="text" name="Nombre" value="<?php echo $employee['Nombre']; ?>"></td>
                    <td><input type="text" name="Puesto" value="<?php echo $employee['Puesto']; ?>"></td>
                  
                    <td><input type="submit" name="edit" value="Actualizar"></td>
                </form>
            </tr>
        <?php endforeach; ?>
    </table>
</body>

</html>
