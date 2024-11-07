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
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");



// FUNCIONES

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

	$data = [];

	$stmt = mysqli_prepare($conexion, "SELECT * FROM `upts`");
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			array_push($data, $row);
		}
		return ['success' => $data];
	}
	$stmt->close();

	return ['error' => 'No hay registros para mostrar'];
}


function nuevo_alter($user, $alter)
{
	global $conexion;

	$stmt_o = $conexion->prepare("INSERT INTO upts (user, qry) VALUES (?, ?)");
	$stmt_o->bind_param("ss", $user, $alter);

	if ($stmt_o->execute()) {
		$id_r = $conexion->insert_id;
		$stmt_o->close();
		return ['success' => $id_r];
	} else {
		return ['error' => 'Error en el registro'];
	}
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

	case ('send_alter'):
		$usr = validar($llave);
		$consulta = $data["consulta"];

		if ($usr) {
			// Validar si la consulta contiene palabras peligrosas
			$consulta_lower = strtolower($consulta);  // Convertir la consulta a minúsculas para hacer la comparación
			$palabras_prohibidas = ['drop', 'delete', 'truncate', 'update', 'select', 'insert', 'alter'];

			// Buscar si alguna palabra prohibida está en la consulta
			foreach ($palabras_prohibidas as $palabra) {
				if (strpos($consulta_lower, $palabra) !== false) {
					echo json_encode(['error' => 'Consulta no permitida']);
					exit;
				}
			}
			// Si pasa la validación, llamar la función correspondiente
			echo json_encode(nuevo_alter($usr, $consulta_lower));
		} else {
			echo json_encode(['error' => 'No tiene permisos de acceso']);
		}
		break;

	default:
		echo json_encode(['error' => 'Accion no especificada']);
		break;
}
