<?php

include 'capaDatos.php';

define('DB_SERVER','<direccion servidor>');
define('DB_NAME','<nombre base de datos>');
define('DB_USER','');
define('DB_PASS','');

ini_set("soap.wsdl_cache_enabled","0");

$server = new SoapServer("services.wsdl");

/**
 * Obtenemos todos los destinos para un Usuario alumno indicado
 * Serán validos aquellos destinos de los que tengamos registradas
 * Asignaturas Extrangeras que convalides con alguna de la titulación del alumno
 * @param $idAlumno Alumno solicitante
 */
function consultarDestinos($idAlumno){
	$retorno = new ArrayDestinos();
	
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$esquema = mysql_select_db(DB_NAME, $conexion);
		if($esquema){
			$query = "SELECT d.nombre AS nombre, p.nombre AS pais, i.nombre AS idioma, d.disponible,".
					 " d.numplazas AS numplazas, n.nombre AS nvlrequerido".
					 " FROM (((((((Usuario u INNER JOIN Matricula m on m.id = u.id)".
					 " INNER JOIN Asignatura a ON a.id = u.asignatura)".
					 " INNER JOIN Convalidacion c ON c.asignatura = a.id)".
					 " INNER JOIN AsignaturaExt ae ON ae.id = c.asignaturaext)".
					 " INNER JOIN Destino d ON d.id = ae.centro)".
					 " INNER JOIN Pais p ON d.pais = p.id)".
					 " INNER JOIN Idioma i ON d.idioma = i.id)".
					 " INNER JOIN Nivel n ON d.nvlrequerido = n.id".
					 " WHERE u.id = ".$idAlumno.";";
			$resultadoQuery = mysql_query(mysql_escape_string($query), $conexion);
			if($resultadoQuery){
				$retorno->errno = 0;
				while($fila = mysql_fetch_row($resultadoQuery)){
					$destinoActual = new ComplexDestino();
					
					$destinoActual->nombre = $fila['nombre'];
					$destinoActual->pais = $fila['pais'];
					$destinoActual->idioma = $fila['idioma'];
					$destinoActual->disponible = $fila['disponible'];
					$destinoActual->numplazas = $fila['numplazas'];
					$destinoActual->nvlrequerido = $fila['nvlrequerido'];
					
					array_push($retorno->destinos, $destinoActual);
				}
			}
			else{
				$retorno->errno = -3;
			}
		}
		else{
			$retorno->errno = -2;
		}
	
		mysql_close($conexion);
	}
	else{
		$retorno->errno = -1;
	}
	
	return $retorno;
}

/**
 * Insertar una nueva solicitud para el Usuario alumno y Destino indicado
 * @param $idAlumno Alumno solicitante
 * @param $idDestino
 */
function crearSolicitud($idAlumno, $idDestino){
	
	$retorno = new GenericResult();
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$bdactual = mysql_select_db(DB_NAME, $conexion);
		$fechaactual = date("d/m/Y");
		
		$query = "INSERT INTO Solicitud VALUES(".$idAlumno.", 
												".$idDestino.", 
												'".$fechaactual."', false);";
		
		$resultadoquery = mysql_query(mysql_escape_string($query), $conexion);
		if($resultadoquery){
			$retorno->errno = 0;
		}
		else{ /*Sentencia SQL incorrecta*/
			$retorno->errno = -2;
		}
		
		mysql_close($conexion);
	}
	else{ /*Fallo en la conexion*/
		$retorno->errno = -1;
	}
	
	return $retorno;
}

/**
 * Insertar un nuevo destino dados sus datos
 * @param $nombre nombre del destino
 * @param $idPais id del pais al que pertenece
 * @param $idIdioma id del idioma hablado
 * @param $disponible disponible Si/No
 * @param $numPlazas plazas disponibles
 * @param $nvlRequerido nivel del idioma requerido
 */
function crearDestino($nombre, $idPais, $idIdioma, $disponible, $numPlazas, $nvlRequerido){
	$retorno = new GenericResult();
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$bdactual = mysql_select_db(DB_NAME, $conexion);
		
		$query = "INSERT INTO Destino('nombre', 'pais', 'idioma', 'disponible', 'numplazas', 'nvlrequerido')".
												" VALUES('".$nombre."', 
												".$idPais.",
												".$idIdioma.",
												".$disponible.",
												".$numPlazas.", 
												".$nvlRequerido.", false);";
		
		$resultadoquery = mysql_query(mysql_escape_string($query), $conexion);
		if($resultadoquery){
			$retorno->errno = 0;
		}
		else{ /*Sentencia SQL incorrecta*/
			$retorno->errno = -2;
		}
		
		mysql_close($conexion);
	}
	else{ /*Fallo en la conexion*/
		$retorno->errno = -1;
	}
	
	return $retorno;	
}

/**
 * Modificar datos de un destino
 * @param $idDestino id del destino
 * @param $nombre nombre del destino
 * @param $idPais id del pais al que pertenece
 * @param $idIdioma id del idioma hablado
 * @param $disponible disponible Si/No
 * @param $numPlazas plazas disponibles
 * @param $nvlRequerido nivel del idioma requerido
 */
function editarDestino($idDestino, $nombre, $idPais, $idIdioma, $disponible, $numPlazas, $nvlRequerido){
	$retorno = new GenericResult();
	
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$esquema = mysql_select_db(DB_NAME, $conexion);
		if($esquema){
			$query = "UPDATE FROM Destino SET nombre='".$nombre."' 
					pais=".$idPais." 
					idioma=".$idIdioma." 
					disponible=".$disponible." 
					numPlazas=".$numPlazas." 
					nvlRequerido=".$nvlRequerido." 
					WHERE id=".$idDestino.";";
			$resultadoQuery = mysql_query(mysql_escape_string($query), $conexion);
			if($resultadoQuery){
				$retorno->errno = 0;
			}
			else{
				$retorno->errno = -3;
			}
		}
		else{
			$retorno->errno = -2;
		}
		
		mysql_close($conexion);
	}
	else{
		$retorno->errno = -1;
	}
	
	return $retorno;
}

/**
 * Borrar un destino dado su Id
 * @param $idDestino id del destino a eliminar
 */
function borrarDestino($idDestino){
	$retorno = new GenericResult();
	
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$esquema = mysql_select_db(DB_NAME, $conexion);
		if($esquema){
			$query = "DELETE FROM Destino WHERE idDestino = ".$idDestino.";";
			$resultadoQuery = mysql_query(mysql_escape_string($query), $conexion);
			if($resultadoQuery){
				$retorno->errno = 0;
			}
			else{
				$retorno->errno = -3;
			}
		}
		else{
			$retorno->errno = -2;
		}
	
		mysql_close($conexion);
	}
	else{
		$retorno->errno = -1;
	}
	
	return $retorno;
}

/**
 * Un usuario Coordinador acepta la solicitud de otro usuario Estudiante
 * @param $idUsuario id del alumno solicitante
 * @param $idDestino id del destino
 */
function aceptarSolicitud($idUsuario, $idDestino){
	/* Basicamente hay que actualizar a True el campo Aceptado
	 * de entidad Solicitud*/
	$retorno = new GenericResult();
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$bdactual = mysql_select_db(DB_NAME, $conexion);
		
		$query = "UPDATE Solicitud SET aceptado = true WHERE idAl=". $idUsuario .
					" AND idDest = ". $idDestino .";";
		
		$resultadoquery = mysql_query(mysql_escape_string($query), $conexion);
		if($resultadoquery){
			$retorno->errno = 0;
		}
		else{ /*Sentencia SQL incorrecta*/
			$retorno->errno = -2;
		}
		
		mysql_close($conexion);
	}
	else{ /*Fallo en la conexion*/
		$retorno->errno = -1;
	}
	
	return $retorno;//TODO
}

/**
 * Obtener una lista de solicitudes que podremos filtrar por Usuario y/o Destino
 * o ninguno de los dos dependiento del valor de los parametros
 * @param $idUsuario id del alumno solicitante
 * @param $idDestino id del destino
 */
function consultarSolicitudes($idUsuario=-1, $idDestino=-1){
	//TODO
	$retorno = new ArraySolicitudes();
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$bdactual = mysql_select_db(DB_NAME, $conexion);
		
		//Armando la consulta SQL
		$query = "SELECT u.nombre AS nomAlumno, s.idAl AS idAl, d.nombre AS nomDestino, s.idDest AS idDest, d.fecha AS fecha, d.aceptado AS aceptado".
				 " FROM (Usuario u INNER JOIN Solicitud s ON u.id = s.idAl)".
				 " INNER JOIN Destino d ON d.id = s.idDest";
		
		if(($idUsuario != -1) && ($idDestino != -1)){
			$query = $query.
				" WHERE s.idAl = ". $idUsuario.
				" AND s.idDest = ". $idDestino;
		}
		else if ($idUsuario != -1) {
			$query = $query.
				" WHERE s.idAl = ". $idUsuario;
		}
		else {
			$query = $query.
				" WHERE s.idDest = ". $idDestino;
		}
		
		$query = $query .";";
		//Fin de consulta SQL
	
		$resultadoquery = mysql_query(mysql_escape_string($query), $conexion);
		if($resultadoquery){
			$retorno->errno = 0;
			while($fila = mysql_fetch_row($resultadoquery)){
				$solicitudActual = new ComplexSolicitud();
					
				$solicitudActual->nomAlumno = $fila['nombre'];
				$solicitudActual->idAl = $fila['idAl'];
				$solicitudActual->nomDestino = $fila['nomDestino'];
				$solicitudActual->idDest = $fila['idDest'];
				$solicitudActual->fecha = $fila['fecha'];
				$solicitudActual->aceptado = $fila['aceptado'];
					
				array_push($retorno->solicitudes, $solicitudActual);
			}
		}
		else{ /*Sentencia SQL incorrecta*/
			$retorno->errno = -2;
		}
	
		mysql_close($conexion);
	}
	else{ /*Fallo en la conexion*/
		$retorno->errno = -1;
	}
	
	return $retorno;
}

$server->addFunction("consultarDestinos");
$server->addFunction("crearSolicitud");
$server->addFunction("crearDestino");
$server->addFunction("editarDestino");
$server->addFunction("borrarDestino");
$server->addFunction("aceptarSolicitud");
$server->addFunction("consultarSolicitudes");

$server->handle();


?>
