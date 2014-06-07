<?php

class GenericResult{
	public $errno;
};

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

class ArrayDestinos{
	public $errno;
	public $destinos;
	
	public function ArrayDestinos(){
		$destinos = array();
	}
};

class ArraySolicitudes{
	public $errno;
	public $solicitudes;

	public function ArraySolicitudes(){
		$solicitudes = array();
	}
};

?>