<?php
include 'capaDatos.php';
include 'connectiondata.php';

ini_set ( "soap.wsdl_cache_enabled", "0" );

$server = new SoapServer ( "services.wsdl" );

/**
 * Obtenemos todos los destinos para un Usuario alumno indicado
 * Ser�n validos aquellos destinos de los que tengamos registradas
 * Asignaturas Extrangeras que convalides con alguna de la titulaci�n del alumno
 * 
 * @param $idAlumno Alumno
 *        	solicitante
 */
function consultarDestinos($idAlumno) {
	$retorno = new ArrayDestinos ();
	
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$esquema = mysql_select_db ( DB_NAME, $conexion );
		if ($esquema) {
			$query = sprintf ( "SELECT DISTINCT d.id AS id, d.nombre AS nombre, p.nombre AS pais, p.id AS idpais,".
								" i.nombre AS idioma, i.id AS id_idioma, d.disponible," . 
								" d.numplazas AS numplazas, n.nombre AS nvlrequerido, n.id AS idnvlrequerido" . 
								" FROM (((((((Usuario u INNER JOIN Matricula m on m.id = u.id)" .
								" INNER JOIN Asignatura a ON a.id = m.asignatura)" . 
								" INNER JOIN Convalidacion c ON c.asignatura = a.id)" . 
								" INNER JOIN AsignaturaExt ae ON ae.id = c.asignaturaext)" . 
								" INNER JOIN Destino d ON d.id = ae.centro)" . 
								" INNER JOIN Pais p ON d.pais = p.id)" . 
								" INNER JOIN Idioma i ON d.idioma = i.id)" . 
								" INNER JOIN Nivel n ON d.nvlrequerido = n.id" . 
								" WHERE u.id = %d AND d.numplazas > 0 AND d.disponible = true AND d.id  NOT IN (SELECT DISTINCT idDest FROM Solicitud WHERE idAl = u.id) ;", $idAlumno );
			logToFile("consultarDestinos.txt", $query);
			$resultadoQuery = mysql_query ( $query, $conexion );
			if ($resultadoQuery) {
				$retorno->errno = 0;
				if (mysql_num_rows ( $resultadoQuery ) != 0) {
					$temparray = array();
					while ( $fila = mysql_fetch_assoc ( $resultadoQuery ) ) {
						$destinoActual = new ComplexDestino ();
						
						$destinoActual->id = $fila ['id'];
						$destinoActual->nombre = $fila ['nombre'];
						$destinoActual->pais = $fila ['pais'];
						$destinoActual->idpais = $fila ['idpais'];
						$destinoActual->idioma = $fila ['idioma'];
						$destinoActual->id_idioma = $fila ['id_idioma'];
						$destinoActual->disponible = $fila ['disponible'];
						$destinoActual->numplazas = $fila ['numplazas'];
						$destinoActual->nvlrequerido = $fila ['nvlrequerido'];
						$destinoActual->idnvlrequerido = $fila ['idnvlrequerido'];
						
						array_push ( $temparray, $destinoActual );
					}
					$retorno->destinos = $temparray;
				} else { // La consulta no devolvi� ning�n resultado
					$retorno->errno = 1;
				}
			} else {
				$retorno->errno = - 3;
			}
		} else {
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	} else {
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Insertar una nueva solicitud para el Usuario alumno y Destino indicado.
 *
 * PRECONDICIONES: La tabla de solicitudes, al igual que el resto de tablas
 * cuya clave primaria es un valor num�rico, debe tener activado el
 * autoincremento.
 *
 * @param $idAlumno Alumno
 *        	solicitante
 * @param
 *        	$idDestino
 */
function crearSolicitud($idAlumno, $idDestino) {
	$retorno = new GenericResult ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		$fechaactual = date ( "Y/m/d" );
		
		$query = sprintf ( "INSERT INTO Solicitud VALUES(%d,%d,'%s', false);", $idAlumno, $idDestino, mysql_escape_string ( $fechaactual ) );
		
		logToFile("crearSolicitud.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Elimina solicitud para el Usuario alumno y Destino indicado.
 *
 * @param $idAlumno Alumno
 *        	solicitante
 * @param
 *        	$idDestino
 */
function borrarSolicitud($idAlumno, $idDestino){
	$retorno = new GenericResult ();
	
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$esquema = mysql_select_db ( DB_NAME, $conexion );
		if ($esquema) {
			$query = sprintf ( "DELETE FROM Solicitud WHERE usuario = %d AND destino = %d;", 
					$idAlumno, $idDestino );
			logToFile("borrarSolicitud.txt", $query);
			$resultadoQuery = mysql_query ( $query, $conexion );
			if ($resultadoQuery) {
				$retorno->errno = 0;
			} else {
				$retorno->errno = - 3;
			}
		} else {
			$retorno->errno = - 2;
		}
	
		mysql_close ( $conexion );
	} else {
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Insertar un nuevo destino dados sus datos
 *
 * PRECONDICIONES: La tabla de destinos, al igual que el resto de tablas
 * cuya clave primaria es un valor num�rico, debe tener activado el
 * autoincremento.
 *
 * @param $nombre nombre
 *        	del destino
 * @param $idPais id
 *        	del pais al que pertenece
 * @param $idIdioma id
 *        	del idioma hablado
 * @param $disponible disponible
 *        	Si/No
 * @param $numPlazas plazas
 *        	disponibles
 * @param $nvlRequerido nivel
 *        	del idioma requerido
 */
function crearDestino($nombre, $idPais, $idIdioma, $disponible, $numPlazas, $nvlRequerido) {
	$retorno = new GenericResult ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		
		$query = sprintf ( "INSERT INTO Destino(nombre, pais, idioma, disponible, numplazas, nvlrequerido)" . " VALUES('%s',%d,%d,%d,%d,%d);", mysql_escape_string ( $nombre ), $idPais, $idIdioma, $disponible, $numPlazas, $nvlRequerido );
		
		logToFile("crearDestino.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Modificar datos de un destino
 * 
 * @param $idDestino id
 *        	del destino
 * @param $nombre nombre
 *        	del destino
 * @param $idPais id
 *        	del pais al que pertenece
 * @param $idIdioma id
 *        	del idioma hablado
 * @param $disponible disponible
 *        	Si/No
 * @param $numPlazas plazas
 *        	disponibles
 * @param $nvlRequerido nivel
 *        	del idioma requerido
 */
function editarDestino($idDestino, $nombre, $idPais, $idIdioma, $disponible, $numPlazas, $nvlRequerido) {
	$retorno = new GenericResult ();
	
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$esquema = mysql_select_db ( DB_NAME, $conexion );
		if ($esquema) {
			$query = sprintf ( "UPDATE Destino SET nombre='%s', 
					pais=%d, idioma=%d, disponible=%d, numPlazas=%d, nvlRequerido=%d 
					WHERE id=%d;", mysql_escape_string ( $nombre ), $idPais, $idIdioma, $disponible, $numPlazas, $nvlRequerido, $idDestino );
			logToFile("editarDestino.txt", $query);
			$resultadoQuery = mysql_query ( $query, $conexion );
			if ($resultadoQuery) {
				$retorno->errno = 0;
			} else {
				$retorno->errno = - 3;
			}
		} else {
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	} else {
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Borrar un destino dado su Id
 *
 * @param $idDestino id
 *        	del destino a eliminar
 */
function borrarDestino($idDestino) {
	$retorno = new GenericResult ();
	
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$esquema = mysql_select_db ( DB_NAME, $conexion );
		if ($esquema) {
			$query = sprintf ( "DELETE FROM Destino WHERE id = %d;", $idDestino );
			logToFile("borrarDestino.txt", $query);
			$resultadoQuery = mysql_query ( $query, $conexion );
			if ($resultadoQuery) {
				$retorno->errno = 0;
			} else {
				$retorno->errno = - 3;
			}
		} else {
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	} else {
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Un usuario Coordinador acepta la solicitud de otro usuario Estudiante
 * 
 * @param $idUsuario id
 *        	del alumno solicitante
 * @param $idDestino id
 *        	del destino
 */
function aceptarSolicitud($idUsuario, $idDestino) {
	/*
	 * Basicamente hay que actualizar a True el campo Aceptado de entidad Solicitud
	 */
	$retorno = new GenericResult ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		
		$query = sprintf ( "UPDATE Solicitud SET aceptado = true WHERE idAl= %d AND idDest = %d;", $idUsuario, $idDestino );
		logToFile("aceptarSolicitud.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Obtener una lista de solicitudes que podremos filtrar por Usuario y/o Destino
 * o ninguno de los dos dependiento del valor de los parametros
 * 
 * @param $idUsuario id
 *        	del alumno solicitante
 * @param $idDestino id
 *        	del destino
 */
function consultarSolicitudes($idUsuario, $idDestino) {
	$retorno = new ArraySolicitudes ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		
		// Armando la consulta SQL
		$query = "SELECT u.nombre AS nomAlumno, s.idAl AS idAl, d.nombre AS nomDestino, s.idDest AS idDest, s.fecha AS fecha, s.aceptado AS aceptado" . " FROM (Usuario u INNER JOIN Solicitud s ON u.id = s.idAl)" . " INNER JOIN Destino d ON d.id = s.idDest";
		
		if (($idUsuario != - 1) && ($idDestino != - 1)) {
			$query = $query . sprintf ( " WHERE s.idAl = %d", $idUsuario ) . sprintf ( " AND s.idDest = %d", $idDestino );
		} else if ($idUsuario != - 1) {
			$query = $query . sprintf ( " WHERE s.idAl = %d", $idUsuario );
		} else {
			$query = $query . sprintf ( " WHERE s.idDest = %d", $idDestino );
		}
		
		$query = $query . ";";
		// Fin de consulta SQL
		logToFile("consultarSolicitudes.txt", $query);
		
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
			if (mysql_num_rows ( $resultadoquery ) != 0) {
				$tempSolicitudes = array();
				while ( $fila = mysql_fetch_assoc ( $resultadoquery ) ) {
					$solicitudActual = new ComplexSolicitud ();
					
					$solicitudActual->nomAlumno = $fila ['nomAlumno'];
					$solicitudActual->idAl = $fila ['idAl'];
					$solicitudActual->nomDestino = $fila ['nomDestino'];
					$solicitudActual->idDest = $fila ['idDest'];
					$solicitudActual->fecha = $fila ['fecha'];
					$solicitudActual->aceptado = $fila ['aceptado'];
					
					array_push ( $tempSolicitudes, $solicitudActual );
				}
				
				$retorno->solicitudes = $tempSolicitudes;
			} else { // La consulta no devolvi� ning�n resultado
				$retorno->errno = 1;
			}
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Obtiene una lista de asignaturas a la que un alumno
 * est� matriculado
 *
 * @param $idAlumno ID
 *        	del Usuario sobre el que hacer la petici�n
 */
function consultarAsignaturasMatriculadas($idAlumno) {
	$retorno = new ArrayAsignaturas();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		
		// Armando la consulta SQL
		$query = sprintf ( "SELECT asig.nombre AS nombre, t.nombre AS titulacion, asig.creditos AS creditos, c.nombre AS coordinador" . 
							" FROM (((Asignatura asig INNER JOIN Usuario c ON c.id = asig.coordinador)" . 
							" INNER JOIN Titulacion t ON t.id = asig.titulacion)" . 
							" INNER JOIN Matricula m ON m.asignatura = asig.id)" . 
							" INNER JOIN Usuario al ON al.id = m.id" . 
							" WHERE al.id = %d and (m.nota<5 or m.nota is null);", $idAlumno );
		// Fin de consulta SQL
		logToFile("consultarAsignaturasMatriculadas.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
			if (mysql_num_rows ( $resultadoquery ) != 0) {
				$tempAsignaturas = array();
				while ( $fila = mysql_fetch_assoc ( $resultadoquery ) ) {
					$asignaturaActual = new ComplexAsignatura ();
					
					$asignaturaActual->id = $fila ['id'];
					$asignaturaActual->nombre = $fila ['nombre'];
					$asignaturaActual->titulacion = $fila ['titulacion'];
					$asignaturaActual->creditos = $fila ['creditos'];
					$asignaturaActual->coordinador = $fila ['coordinador'];
					
					array_push ( $tempAsignaturas, $asignaturaActual );
				}
				$retorno->asignaturas = $tempAsignaturas;
			} else { // La consulta no devolvi� ning�n resultado
				$retorno->errno = 1;
			}
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = -2;
		}
		
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = -1;
	}
	
	return $retorno;
}

/**
 * Obtiene una lista de asignaturas extrangeras asociadas
 * a un alumno.
 *
 * @param $idAlumno ID
 *        	del alumno a hacer la petici�n.
 */
function consultarExtrangerasAlumno($idAlumno) {
	$retorno = new ArrayAsignaturasExt ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		
		// Armando la consulta SQL
		$query = sprintf ( "SELECT aex.nombre AS nombre, aex.creditos AS creditos, des.nombre AS centro" . " FROM (((((Asignatura asig INNER JOIN Titulacion t ON t.id = asig.titulacion)" . " INNER JOIN Matricula m ON m.asignatura = asig.id)" . " INNER JOIN Usuario al ON al.id = m.id)" . " INNER JOIN Convalidacion co ON co.asignatura = asig.id)" . " INNER JOIN AsignaturaExt aex ON co.AsignaturaExt aex.id)" . " INNER JOIN Destino des ON des.id = aex.centro" . " WHERE al.id = %d;", mysql_escape_string ( $idAlumno ) );
		// Fin de consulta SQL
		logToFile("consultarExtrangerasAlumno.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
			if (mysql_num_rows ( $resultadoquery ) != 0) {
				$tempAsignaturas = array();
				while ( $fila = mysql_fetch_assoc ( $resultadoquery ) ) {
					$asignaturaActual = new ComplexAsignaturaExt ();
					
					$asignaturaActual->nombre = $fila ['nombre'];
					$asignaturaActual->creditos = $fila ['creditos'];
					$asignaturaActual->centro = $fila ['centro'];
					
					array_push ( $tempAsignaturas, $asignaturaActual );
				}
				$retorno->asignaturas = $tempAsignaturas;
			} else { // La consulta no devolvi� ning�n valor
				$retorno->errno = 1;
			}
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Funci�n que devuelve un usuario registrado en el
 * sistema en caso de que est� registrado
 *
 * PRECONDICIONES: La contrase�a del usuario deber�a
 * ir ya encriptada desde el cliente. La base de datos
 * s�lo almacenar� los hashes correspondientes.
 *
 * @param $nick Apodo
 *        	el usuario en el sistema
 * @param $passwd Contrase�a
 *        	del usuario en el sistema
 */
function loginUsuario($nick, $passwd) {
	$retorno = new ComplexUsuario ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		
		// Armando la consulta SQL
		$query = sprintf ( "SELECT *" . " FROM Usuario" . " WHERE nick = '%s' AND passwd = '%s';", mysql_escape_string ( $nick ), mysql_escape_string ( $passwd ) );
		// Fin de consulta SQL
		logToFile("loginUsuario.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			if (mysql_num_rows ( $resultadoquery ) == 1) {
				$retorno->errno = 0;
				$fila = mysql_fetch_assoc ( $resultadoquery );
				$retorno->id = $fila ['id'];
				$retorno->nombre = $fila ['nombre'];
				$retorno->apellidos = $fila ['apellidos'];
				$retorno->nif = $fila ['nif'];
				$retorno->rol = $fila ['rol'];
				$retorno->direccion = $fila ['direccion'];
				$retorno->poblacion = $fila ['poblacion'];
				$retorno->nick = $nick;
				$retorno->passwd = $passwd;
				$retorno->titulacion = $fila ['titulacion'];
			} else { // La consulta devuelve uno o ning�n resultado
				$retorno->errno = 1;
			}
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Obtenemos todos los destinos para que un usuario
 * coordinador pueda modificarlos m�s adelante.
 *
 */
function obtenerDestinos($entero){
	$retorno = new ArrayDestinos ();
	
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$esquema = mysql_select_db ( DB_NAME, $conexion );
		if ($esquema) {
			$query = sprintf ( "SELECT d.id AS id, d.nombre AS nombre, p.nombre AS pais, p.id AS idpais,".
					" i.nombre AS idioma, i.id AS id_idioma, d.disponible," . 
					" d.numplazas AS numplazas, n.nombre AS nvlrequerido, n.id AS idnvlrequerido" .
					" FROM ((Destino d INNER JOIN Pais p ON d.pais = p.id)" .
					" INNER JOIN Idioma i ON d.idioma = i.id)" .
					" INNER JOIN Nivel n ON d.nvlrequerido = n.id;" );
			logToFile("obtenerDestinos.txt", $query);
			$resultadoQuery = mysql_query ( $query, $conexion );
			if ($resultadoQuery) {
				$retorno->errno = 0;
				if (mysql_num_rows ( $resultadoQuery ) != 0) {
					$temparray = array();
					while ( $fila = mysql_fetch_assoc ( $resultadoQuery ) ) {
						$destinoActual = new ComplexDestino ();
	
						$destinoActual->id = $fila ['id'];
						$destinoActual->nombre = $fila ['nombre'];
						$destinoActual->pais = $fila ['pais'];
						$destinoActual->idpais = $fila ['idpais'];
						$destinoActual->idioma = $fila ['idioma'];
						$destinoActual->id_idioma = $fila ['id_idioma'];
						$destinoActual->disponible = $fila ['disponible'];
						$destinoActual->numplazas = $fila ['numplazas'];
						$destinoActual->nvlrequerido = $fila ['nvlrequerido'];
						$destinoActual->idnvlrequerido = $fila ['idnvlrequerido'];
	
						array_push ( $temparray, $destinoActual );
					}
					$retorno->destinos = $temparray;
				} else { // La consulta no devolvi� ning�n resultado
					$retorno->errno = 1;
				}
			} else {
				$retorno->errno = - 3;
			}
		} else {
			$retorno->errno = - 2;
		}
	
		mysql_close ( $conexion );
	} else {
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Agrega una asignatura extrangera a una solicitud de Erasmus
 * 
 * @param $idAlumno Id del alumno, clave conjunta
 * @param $idDestino Id del destino, clave conjunta
 * @param $idAsignaturaExt Id de la asignatura que se imparte en
 * 			el extrangero
 */
function agregarAsignaturaSolicitud($idAlumno, $idDestino, $idAsignaturaExt){
	$retorno = new ArrayAsignaturasExt();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
	
		$query = sprintf ( "INSERT INTO AsigPrecontrato VALUES (%d, %d, %d);", 
				$idAlumno, $idDestino, $idAsignaturaExt );
		logToFile("agregarAsignaturaSolicitud.txt", $query);
		try{
			$resultadoquery = mysql_query ( $query, $conexion );
			if ($resultadoquery) {
				$retorno->errno = 0;
			} else { /* Sentencia SQL incorrecta */
				$retorno->errno = - 2;
			}
		} 
		catch (Exception $msqle){ //Violaci�n de integridad
			$retorno->errno = 2;
		}
	
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Devuelve las asignaturas extrangeras para una solicitud
 * 
 * @param $idAlumno Id del alumno
 * @param $idDestino Id del destino
 */
function obtenerAsignaturasSolicitud($idAlumno, $idDestino){
	$retorno = new ArrayAsignaturasExt();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
	
		$query = sprintf ( "SELECT aex.id AS id, aex.nombre AS nombre, aex.creditos AS creditos, d.nombre AS centro, aex.centro AS idcentro
							FROM (AsigPrecontrato pre INNER JOIN AsignaturaExt aex ON aex.id = pre.asignaturaex)
							INNER JOIN Destino d ON aex.centro = d.id
							WHERE pre.idAlumno = %d AND pre.idDestino = %d;",
				$idAlumno, $idDestino);
		logToFile("obtenerAsignaturasSolicitud.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
			if (mysql_num_rows ( $resultadoquery ) != 0) {
				$temparray = array ();
				while ( $fila = mysql_fetch_assoc ( $resultadoquery ) ) {
					$asignaturaActual = new ComplexAsignaturaExt();
			
					$asignaturaActual->id = $fila ['id'];
					$asignaturaActual->nombre = $fila ['nombre'];
					$asignaturaActual->creditos = $fila ['creditos'];
					$asignaturaActual->centro = $fila ['centro'];
					$asignaturaActual->idcentro = $fila ['idcentro'];
						
					array_push ( $temparray, $asignaturaActual );
				}
				$retorno->asignaturas = $temparray;
			} else { // La consulta no devolvi� ning�n resultado
				$retorno->errno = 1;
			}
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
	
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Obtiene la lista de asignaturas a las que un alumno puede optar
 * a un destino en base a sus asignaturas matriculadas
 * 
 * @param $idAlumno Id del alumno
 * @param $idDestino Id del destino
 */
function obtenerAsignaturasSolicitables($idAlumno, $idDestino){
	$retorno = new ArrayAsignaturasExt();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
	
		$query = sprintf ( "SELECT aex.id AS id, aex.nombre AS nombre, aex.creditos AS creditos, d.nombre AS centro, aex.centro AS idcentro" .
							" FROM ((((Usuario usu INNER JOIN Matricula mat ON usu.id = mat.id)" .
							" INNER JOIN Asignatura asig ON asig.id = mat.asignatura)" .
							" INNER JOIN Convalidacion conv ON asig.id = conv.asignatura)" .
							" INNER JOIN AsignaturaExt aex ON aex.id = conv.asignaturaext)" .
							" INNER JOIN Destino d ON aex.centro = d.id" .
							" WHERE usu.id = %d AND aex.centro = %d AND aex.id  NOT IN (SELECT asignaturaext 
																					 	FROM AsigPrecontrato prec 
																					 	WHERE aex.centro = prec.destino AND usu.id = prec.usuario);",
							$idAlumno, $idDestino);
		logToFile("obtenerAsignaturasSolicitables.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
			if (mysql_num_rows ( $resultadoquery ) != 0) {
				$temparray = array ();
				while ( $fila = mysql_fetch_assoc ( $resultadoquery ) ) {
					$asignaturaActual = new ComplexAsignaturaExt();
			
					$asignaturaActual->id = $fila ['id'];
					$asignaturaActual->nombre = $fila ['nombre'];
					$asignaturaActual->creditos = $fila ['creditos'];
					$asignaturaActual->centro = $fila ['centro'];
					$asignaturaActual->idcentro = $fila ['idcentro'];
			
					array_push ( $temparray, $asignaturaActual );
				}
				$retorno->asignaturas = $temparray;
			} else { // La consulta no devolvi� ning�n resultado
				$retorno->errno = 1;
			}
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
	
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * Obtiene una lista de precontratos en base a la representaci�n
 * que tiene en el cliente a implementar
 * 
 * @param $idAlumno No se utiliza
 * @return ArrayPrecontratos Lista de precontratos
 */
function obtenerPrecontratos($idAlumno){
	/**
	 * Esta funci�n va en dos partes: la primera es obtener todas las
	 * solicitudes a precontrato, la segunda es armar todas las asignaturas
	 * a enviar
	 */
	
	$retorno = new ArrayPrecontratos ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		// Primera parte
		// Armando la consulta SQL
		$query = "SELECT u.nombre AS nomAlumno, s.idAl AS idAlumno," .
				 " d.nombre AS nomDestino, s.idDest AS idDestino," . 
				 " tit.nombre AS titulacion" . 
				 " FROM ((Usuario u INNER JOIN Solicitud s ON u.id = s.idAl)" . 
			 	 " INNER JOIN Destino d ON d.id = s.idDest)" .
			 	 " INNER JOIN Titulacion tit ON tit.id = u.titulacion" .
				 " WHERE s.aceptado=false;";
		// Fin de consulta SQL
		logToFile("obtenerPrecontratos.txt", $query);
		
		$resultadoquery = mysql_query ( $query, $conexion );
		
		if ($resultadoquery) {
			$retorno->errno = 0;
			if (mysql_num_rows ( $resultadoquery ) != 0) {
				$tempPrecontrato = array();
				while ( $fila = mysql_fetch_assoc ( $resultadoquery ) ) {
					$solicitudActual = new ComplexPrecontrato ();
					
					$solicitudActual->nomAlumno = $fila ['nomAlumno'];
					$solicitudActual->idAlumno = $fila ['idAlumno'];
					$solicitudActual->nomDestino = $fila ['nomDestino'];
					$solicitudActual->idDestino = $fila ['idDestino'];
					$solicitudActual->titulacionAlumno = $fila ['titulacion'];
					
						
					array_push ( $tempPrecontrato, $solicitudActual );
				}
		
				$retorno->precontratos = $tempPrecontrato;
				logToFile("obtenerPrecontratos.json", json_encode($retorno));
			} else { // La consulta no devolvi� ning�n resultado
				$retorno->errno = 1;
			}
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
		
		mysql_close ( $conexion );
	}else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}


function obtenerPrecontratosDeAlumno($idAlumno){
	/**
	 * Esta funci�n va en dos partes: la primera es obtener todas las
	 * solicitudes a precontrato, la segunda es armar todas las asignaturas
	 * a enviar
	 */

	$retorno = new ArrayPrecontratos ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		// Primera parte
		// Armando la consulta SQL
		$query = sprintf("SELECT u.nombre AS nomAlumno, s.idAl AS idAlumno, d.nombre AS nomDestino, s.idDest AS idDestino,  tit.nombre AS titulacion
				  FROM ((Usuario u INNER JOIN Solicitud s ON u.id = s.idAl)
				  INNER JOIN Destino d ON d.id = s.idDest) INNER JOIN Titulacion tit ON tit.id = u.titulacion
				  WHERE s.aceptado=false and u.id=%d;", $idAlumno);
		// Fin de consulta SQL
		logToFile("obtenerPrecontratosDeAlumno.txt", $query);

		$resultadoquery = mysql_query ( $query, $conexion );

		if ($resultadoquery) {
			$retorno->errno = 0;
			if (mysql_num_rows ( $resultadoquery ) != 0) {
				$tempPrecontrato = array();
				while ( $fila = mysql_fetch_assoc ( $resultadoquery ) ) {
					$solicitudActual = new ComplexPrecontrato ();
						
					$solicitudActual->nomAlumno = $fila ['nomAlumno'];
					$solicitudActual->idAlumno = $fila ['idAlumno'];
					$solicitudActual->nomDestino = $fila ['nomDestino'];
					$solicitudActual->idDestino = $fila ['idDestino'];
					$solicitudActual->titulacionAlumno = $fila ['titulacion'];
						

					array_push ( $tempPrecontrato, $solicitudActual );
				}

				$retorno->precontratos = $tempPrecontrato;
				logToFile("obtenerPrecontratos.json", json_encode($retorno));
			} else { // La consulta no devolvi� ning�n resultado
				$retorno->errno = 1;
			}
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}

		mysql_close ( $conexion );
	}else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}

	return $retorno;
}

/**
 * Crea una instancia de alumno en la BBDD
 * 
 * @param $nombre del alumno
 * @param $apellidos del alumno
 * @param $nif del alumno
 * @param $direccion del alumno
 * @param $poblacion del alumno
 * @param $nick del alumno
 * @param $passwd del alumno
 * @param $titulacion elegida por del alumno
 * @return GenericResult
 */
function crearAlumno($nombre, $apellidos, $nif, $direccion, $poblacion, $nick, $passwd, $titulacion){
	$retorno = new GenericResult ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );

		$rol=1;

		$query = sprintf ( "INSERT INTO Usuario (nombre, apellidos, nif, rol, direccion, poblacion, nick, passwd, titulacion)" . " VALUES('%s','%s','%s',%d,'%s','%s','%s', '%s', %d);", mysql_escape_string ($nombre), mysql_escape_string($apellidos), mysql_escape_string($nif), $rol, mysql_escape_string($direccion), mysql_escape_string($poblacion), mysql_escape_string($nick), mysql_escape_string($passwd), $titulacion );
		logToFile("crearAlumno.txt", $query);

		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}

		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}

	return $retorno;
}

/**
 * 
 * @param $nombre del coordinador
 * @param $apellidos del coordinador
 * @param $nif	del coordinador
 * @param $direccion del coordinador
 * @param $poblacion del coordinador
 * @param $nick del coordinador
 * @param $passwd del coordinador
 * @param $titulacion del coordinador
 * @return GenericResult
 */
function crearCoordinador($nombre, $apellidos, $nif, $direccion, $poblacion, $nick, $passwd){
	$retorno = new GenericResult ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );

		$rol=2;

		$query = sprintf ( "INSERT INTO Usuario (nombre, apellidos, nif, rol, direccion, poblacion, nick, passwd)" . " VALUES('%s','%s','%s',%d,'%s','%s','%s', '%s');", mysql_escape_string ($nombre), mysql_escape_string($apellidos), mysql_escape_string($nif), $rol, mysql_escape_string($direccion), mysql_escape_string($poblacion), mysql_escape_string($nick), mysql_escape_string($passwd));
		logToFile("crearCoordinador.txt", $query);

		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}

		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}

	return $retorno;
}

/**
 * 
 * @param $file
 * @param $text
 */
function logToFile($file, $text){
	$fichero = fopen('logs/'.$file, "a");
	$fecha = date('d/m/Y H:i:s');

	fwrite($fichero, $fecha.' -> '.$text. "\r\n");
	fflush($fichero);
	fclose($fichero);
}

/**
 * 
 * @param $idAlumno alumno en cuestion
 * @param $idAsignatura asignatura a matricular
 * @param $quiereConval convalidar asignatura Si/No
 * @return GenericResult
 */
function matricularAsignatura($idAlumno, $idAsignatura, $quiereConval){
	$retorno = new GenericResult ();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
		$fechaactual = date ( "Y/m/d" );
	
		$nota=0; //Empiezas con un 0
		$query = sprintf ( "INSERT INTO matricula (id, asignatura, fecha, nota, quiereconval) 
							VALUES(%d,%d,'%s',%d,%d);", $idAlumno, $idAsignatura, mysql_escape_string ( $fechaactual ), $nota, $quiereConval);
	
		logToFile("matricularAsignatura.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
	
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

/**
 * 
 * @param $idAlumno Alumno interesado
 * @return ArrayAsignaturas Lista de asignaturas que el alumno puede matricular
 */
function obtenerAsignaturasParaMatricular($idAlumno){
	$retorno = new ArrayAsignaturas();
	$conexion = mysql_connect ( DB_SERVER, DB_USER, DB_PASS );
	if ($conexion) {
		$bdactual = mysql_select_db ( DB_NAME, $conexion );
	
		$query = sprintf ( "SELECT asig.id AS id, asig.nombre AS nombre, tit.nombre AS titulacion, asig.creditos AS creditos,
						 coord.nombre AS coordinador_nombre, coord.apellidos AS coordinador_apellidos
						FROM (asignatura asig INNER JOIN titulacion tit ON asig.titulacion = tit.id)
						INNER JOIN Usuario coord ON coord.id = asig.coordinador
						WHERE asig.id NOT IN (SELECT asignatura FROM matricula WHERE id=%d)
						AND asig.titulacion IN (SELECT titulacion FROM usuario WHERE id=%d);",
							$idAlumno, $idAlumno);
		logToFile("obtenerAsignaturasParaMatricular.txt", $query);
		$resultadoquery = mysql_query ( $query, $conexion );
		if ($resultadoquery) {
			$retorno->errno = 0;
			if (mysql_num_rows ( $resultadoquery ) != 0) {
				$temparray = array ();
				while ( $fila = mysql_fetch_assoc ( $resultadoquery ) ) {
					$asignaturaActual = new ComplexAsignatura();
						
					$asignaturaActual->id = $fila ['id'];
					$asignaturaActual->nombre = $fila ['nombre'];
					$asignaturaActual->titulacion = $fila ['titulacion'];
					$asignaturaActual->creditos = $fila ['creditos'];
					
					$nomCoordinador = $fila ['coordinador_nombre'] . " " . $fila ['coordinador_apellidos'];
					$asignaturaActual->coordinador = $nomCoordinador;
	
					array_push ( $temparray, $asignaturaActual );
					
					logToFile("asignaturaLocal.json", json_encode($asignaturaActual) );
				}
				$retorno->asignaturas = $temparray;
			} else { // La consulta no devolvi� ning�n resultado
				$retorno->errno = 1;
			}
		} else { /* Sentencia SQL incorrecta */
			$retorno->errno = - 2;
		}
	
		mysql_close ( $conexion );
	} else { /* Fallo en la conexion */
		$retorno->errno = - 1;
	}
	
	return $retorno;
}

$server->addFunction ( "matricularAsignatura" );
$server->addFunction ( "obtenerAsignaturasParaMatricular" );
$server->addFunction ( "consultarDestinos" );
$server->addFunction ( "crearSolicitud" );
$server->addFunction ( "borrarSolicitud" );
$server->addFunction ( "crearDestino" );
$server->addFunction ( "editarDestino" );
$server->addFunction ( "borrarDestino" );
$server->addFunction ( "aceptarSolicitud" );
$server->addFunction ( "consultarSolicitudes" );
$server->addFunction ( "consultarAsignaturasMatriculadas" );
$server->addFunction ( "consultarExtrangerasAlumno" );
$server->addFunction ( "loginUsuario" );
$server->addFunction ( "obtenerDestinos" );
$server->addFunction ( "agregarAsignaturaSolicitud" );
$server->addFunction ( "obtenerAsignaturasSolicitables" );
$server->addFunction ( "obtenerPrecontratos" );
$server->addFunction ( "obtenerPrecontratosDeAlumno" );
$server->addFunction ( "crearAlumno" );
$server->addFunction ( "crearCoordinador" );


$server->handle ();

?>
