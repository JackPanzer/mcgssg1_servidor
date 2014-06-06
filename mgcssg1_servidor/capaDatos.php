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
	
};

class ArrayDestinos{
	public $errno;
	public $destinos;
	
	public function ArrayDestinos(){
		$destinos = array();
	}
};

?>