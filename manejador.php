<?php

define('HOST', 'localhost');
define('DB', 'bd_sigob_updates');
define('CHARSET', 'utf8mb4');
//define('USER', 'root');
define('USER', 'user_bd_sig_upt');
//define('PASSWORD', "");
define('PASSWORD', "tD~R};w#;lHB");

$conexion = new mysqli(constant('HOST'), constant('USER'), constant('PASSWORD'), constant('DB'));
$conexion->set_charset(constant('CHARSET'));

if ($conexion->connect_error) {
	die('Error de conexion: ' . $conexion->connect_error);
}
date_default_timezone_set('America/Manaus');

// Conectar a la BD


function validar($llave)
{
	global $conexion;

	$stmt = mysqli_prepare($conexion, "SELECT * FROM `salt` WHERE llave = ?");
	$stmt->bind_param('s', $llave);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			return $row['user'];
		}
	}
	$stmt->close();

	return false;
} // Se usa para verificar la clave


function listaActualizaciones()
{
	global $conexion;

	$stmt = mysqli_prepare($conexion, "SELECT * FROM `upts`");
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			return ['success' => $row];
		}
	}
	$stmt->close();

	return ['error' => 'No hay registros para mostrar'];
}




header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);
$accion = $data["accion"];
$llave = $data["llave"];

switch ($accion) {
	case ('validar'):
		$usr = validar($llave);

		if ($usr) {
			echo json_encode(listaActualizaciones());
		} else {
			echo json_encode(['error' => 'No tiene permisos de acceso']);
		}
		break;


	default:
		echo json_encode(['error' => 'Accion no especificada']);
}
