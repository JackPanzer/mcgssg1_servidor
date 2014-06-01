<?php

include 'capaDatos.php';

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
}

/**
 * Insertar una nueva solicitud para el Usuario alumno y Destino indicado
 * @param $idAlumno Alumno solicitante
 * @param $idDestino
 */
function crearSolicitud($idAlumno, $idDestino){
	//TODO
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
 * Insertar un nuevo destino dados sus datos
 * @param $nombre nombre del destino
 * @param $idPais id del pais al que pertenece
 * @param $idIdioma id del idioma hablado
 * @param $disponible disponible Si/No
 * @param $numPlazas plazas disponibles
 * @param $nvlRequerido nivel del idioma requerido
 */
function crearDestino($nombre, $idPais, $idIdioma, $disponible, $numPlazas, $nvlRequerido){
	//TODO	
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
}

/**
 * Borrar un destino dado su Id
 * @param $idDestino id del destino a eliminar
 */
function borrarDestino($idDestino){
	//TODO
}

$server->addFunction("consultarDestinos");
$server->addFunction("crearSolicitud");
$server->addFunction("crearDestino");
$server->addFunction("editarDestino");
$server->addFunction("borrarDestino");

$server->handle();


?>