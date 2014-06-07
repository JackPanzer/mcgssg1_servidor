<?php

/**
 * Clase que devuelve un valor de retorno de una operaci�n.
 * Es usado principalmente cuando hay que realizar
 * operaciones sobre la BBDD que no devuelven informaci�n
 * como un UPDATE o una operaci�n de INSERT.
 * 
 * El campo errno estar� presente en todas las estructuras de
 * datos utilizadas para hacer las veces de depuraci�n y para un
 * desarrollo m�s c�modo.
 *
 */
class GenericResult{
	public $errno;
	
	public function GenericResult(){
		$errno = 0;
	}
};

/**
 * La clase representa la informaci�n de m�s inter�s para
 * la aplicaci�n que se est� implementando con respecto a
 * una consulta a los destinos disponibles.
 * 
 * Esto incluye el valor literal de los campos a los que hace
 * referencia.
 * 
 */
class ComplexDestino{
	
	public $errno;
	public $nombre;
	public $pais;
	public $idioma;
	public $disponible;
	public $numplazas;
	public $nvlrequerido;
	
	public function ComplexDestino(){
		$errno = 0;
	}
	
};

/**
 * Representa la informaci�n de inter�s de
 * una consulta a una solicitud.
 * 
 */
class ComplexSolicitud{
	public $errno;
	public $nomAlumno;
	public $idAl;
	public $nomDestino;
	public $idDest;
	public $fecha;
	public $aceptado;
	
	public function ComplexSolicitud(){
		$errno = 0;
	}
};

/**
 * Representa una lista de destinos.
 */
class ArrayDestinos{
	public $errno;
	public $destinos;
	
	public function ArrayDestinos(){
		$errno = 0;
		$destinos = array();
	}
};

/**
 * Representa una lista de solicitudes de ERASMUS.
 */
class ArraySolicitudes{
	public $errno;
	public $solicitudes;

	public function ArraySolicitudes(){
		$errno = 0;
		$solicitudes = array();
	}
};

/**
 * Representa la informaci�n de una asignatura.
 * 
 */
class ComplexAsignatura{
	public $errno;
	
	public $nombre;
	public $titulacion;
	public $creditos;
	public $coordinador;
	
	public function ComplexAsignatura(){
		$errno = 0;
	}
};

/**
 * Representa una lista de asignaturas
 * 
 */
class ArrayAsignaturas{
	public $errno;
	
	public $asignaturas;
	
	public function ArrayAsignatura(){
		$errno = 0;
		$asignaturas = array();
	}
}

?>