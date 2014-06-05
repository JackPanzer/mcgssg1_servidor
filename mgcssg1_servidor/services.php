<?php

include 'capaDatos.php';

define('DB_SERVER','<direccion servidor>');
define('DB_NAME','<nombre base de datos>');
define('DB_USER','');
define('DB_PASS','');

ini_set("soap.wsdl_cache_enabled","0");

$server = new SoapServer("nuestroWSDL.wsdl");

/**
 * Obtenemos todos los destinos para un Usuario alumno indicado
 * Serán validos aquellos destinos de los que tengamos registradas
 * Asignaturas Extrangeras que convalides con alguna de la titulación del alumno
 * @param $idAlumno Alumno solicitante
 */
function consultarDestinos($idAlumno){
	//TODO
	$retorno;
	
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$esquema = mysql_select_db(DB_NAME, $conexion);
		if($esquema){
			$query = "";
			$resultadoQuery = mysql_query(mysql_escape_string($query), $conexion);
			if($resultadoQuery){
				$retorno = 0;
			}
			else{
				$retorno = -3;
			}
		}
		else{
			$retorno = -2;
		}
	
		mysql_close($conexion);
	}
	else{
		$retorno = -1;
	}
	
	return $retorno;
}

/**
 * Insertar una nueva solicitud para el Usuario alumno y Destino indicado
 * @param $idAlumno Alumno solicitante
 * @param $idDestino
 */
function crearSolicitud($idAlumno, $idDestino){
	
	$retorno;
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$bdactual = mysql_select_db(DB_NAME, $conexion);
		$fechaactual = date("d/m/Y");
		
		$query = "INSERT INTO Solicitud VALUES(".$idAlumno.", 
												".$idDestino.", 
												'".$fechaactual."', false);";
		
		$resultadoquery = mysql_query(mysql_escape_string($query), $conexion);
		if($resultadoquery){
			$retorno = 0;
		}
		else{ /*Sentencia SQL incorrecta*/
			$retorno = -2;
		}
		
		mysql_close($conexion);
	}
	else{ /*Fallo en la conexion*/
		$retorno = -1;
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
	$retorno;
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$bdactual = mysql_select_db(DB_NAME, $conexion);
		
		$query = "INSERT INTO Destino VALUES('".$nombre."', 
												".$idPais.",
												".$idIdioma.",
												".$disponible.",
												".$numPlazas.", 
												".$nvlRequerido.", false);";
		
		$resultadoquery = mysql_query(mysql_escape_string($query), $conexion);
		if($resultadoquery){
			$retorno = 0;
		}
		else{ /*Sentencia SQL incorrecta*/
			$retorno = -2;
		}
		
		mysql_close($conexion);
	}
	else{ /*Fallo en la conexion*/
		$retorno = -1;
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
	//TODO
	$retorno;
	
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$esquema = mysql_select_db(DB_NAME, $conexion);
		if($esquema){
			$query = "UPDATE FROM Destino SET nombre='".$nombre."' 
					idPais=".$idPais." 
					idIdioma=".$idIdioma." 
					disponible=".$disponible." 
					numPlazas=".$numPlazas." 
					nvlRequerido=".$nvlRequerido." 
					WHERE idDetino=".$idDestino.";";
			$resultadoQuery = mysql_query(mysql_escape_string($query), $conexion);
			if($resultadoQuery){
				$retorno = 0;
			}
			else{
				$retorno = -3;
			}
		}
		else{
			$retorno = -2;
		}
		
		mysql_close($conexion);
	}
	else{
		$retorno = -1;
	}
	
	return $retorno;
}

/**
 * Borrar un destino dado su Id
 * @param $idDestino id del destino a eliminar
 */
function borrarDestino($idDestino){
	//TODO
	$retorno;
	
	$conexion = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
	if($conexion){
		$esquema = mysql_select_db(DB_NAME, $conexion);
		if($esquema){
			$query = "DELETE FROM Destino WHERE idDestino = ".$idDestino.";";
			$resultadoQuery = mysql_query(mysql_escape_string($query), $conexion);
			if($resultadoQuery){
				$retorno = 0;
			}
			else{
				$retorno = -3;
			}
		}
		else{
			$retorno = -2;
		}
	
		mysql_close($conexion);
	}
	else{
		$retorno = -1;
	}
	
	return $retorno;
}

$server->addFunction("consultarDestinos");
$server->addFunction("crearSolicitud");
$server->addFunction("crearDestino");
$server->addFunction("editarDestino");
$server->addFunction("borrarDestino");

$server->handle();


?>