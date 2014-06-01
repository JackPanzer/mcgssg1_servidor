<?php

class ComplexDestino{
	
	public $facultad;
	public $titul;
	public $coordinador;
	public $telefonoDestino;
	public $fax;
	public $emailCoordinador;
	public $curso;
	
};

class ComplexCPRA{
	
	public $facultad;
	public $titul;
	public $coordinador;
	public $telefonoUHU;
	public $fax;
	public $emailCorrdinador;
	public $apellidos;
	public $nombre;
	public $emailAlumno;
	public $telefonoAlumno;
	public $destino;
	public $asigDestino;
	public $asigUHU;
	public $firmaAlumno;
	public $firmaCoordinador;
	
};

class ComplexAsignatura{
	
	public $codigo;
	public $denominacion;
	public $creditos;
	public $totalCreditos;
	
};

class ComplexContrato{
	
	public $destino;
	public $cpra;
};

class ArrayAsig{
	public $asignaturas;
};

class ArrayPrecontrato{
	public $precontrato;
};

?>