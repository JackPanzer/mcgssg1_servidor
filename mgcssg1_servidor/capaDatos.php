<?php

/**
 * Clase que devuelve un valor de retorno de una operación.
 * Es usado principalmente cuando hay que realizar
 * operaciones sobre la BBDD que no devuelven información
 * como un UPDATE o una operación de INSERT.
 * 
 * El campo errno estará presente en todas las estructuras de
 * datos utilizadas para hacer las veces de depuración y para un
 * desarrollo más cómodo.
 *
 */
class GenericResult{
	public $errno;
	
	public function GenericResult(){
		$errno = 0;
	}
};

/**
 * La clase representa la información de más interés para
 * la aplicación que se está implementando con respecto a
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
 * Representa la información de interés de
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
 * Representa la información de una asignatura.
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