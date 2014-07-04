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
	public $errno; //int
	
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
	
	public $errno; //int
	public $id; //int
	public $nombre; //string
	public $pais; //string
	public $idpais; //int
	public $idioma; //string
	public $id_idioma; //int
	public $disponible; //bool
	public $numplazas; //int
	public $nvlrequerido; //string
	public $idnvlrequerido; //int
	
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
	public $errno; //int
	public $nomAlumno; //string
	public $idAl; //int
	public $nomDestino; //string
	public $idDest; //int
	public $fecha; //date
	public $aceptado; //bool
	
	public function ComplexSolicitud(){
		$errno = 0;
	}
};

/**
 * Representa una lista de destinos.
 */
class ArrayDestinos{
	public $errno; //int
	public $destinos; //Vector<ComplexDestino>
	
	public function ArrayDestinos(){
		$errno = 0;
		$destinos = array();
	}
};

/**
 * Representa una lista de solicitudes de ERASMUS.
 */
class ArraySolicitudes{
	public $errno; //int
	public $solicitudes; //Vector<ComplexSolicitud>

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
	public $errno; //int
	
	public $idAsig;
	public $nombre; //string
	public $titulacion; //string
	public $creditos; //int
	public $coordinador; //int
	
	//public $asignaturas; //ArrayAsignaturaExt
	
	public function ComplexAsignatura(){
		$errno = 0;
	}
};

/**
 * Representa una lista de asignaturas
 * 
 */
class ArrayAsignaturas{
	public $errno; //int
	
	public $asignaturas; //Vector<ComplexAsignatura>
	
	public function ArrayAsignatura(){
		$errno = 0;
		$asignaturas = array();
	}
}

/**
 * Representa la información de una asignatura.
 *
 */
class ComplexAsignaturaExt{
	public $errno; //int

	public $id; //int
	public $nombre; //string
	public $creditos; //int
	public $centro; //string
	public $idcentro; //int

	public function ComplexAsignaturaExt(){
		$errno = 0;
	}
};

/**
 * Representa una lista de asignaturas
 *
 */
class ArrayAsignaturasExt{
	public $errno; //int

	public $asignaturas; //Vector<ComplexAsignaturaExt>

	public function ArrayAsignaturaExt(){
		$errno = 0;
		$asignaturas = array();
	}
}

/**
 * Representa a un usuario registrado en el sistema
 *
 */
class ComplexUsuario {
	public $errno; //int
	
	public $id; //int
	public $nombre; //string
	public $apellidos; //string
	public $nif; //string
	public $rol; //int
	public $direccion; //string
	public $poblacion; //string
	public $nick; //string
	public $passwd; //string
	public $titulacion; //int
	
	public function ComplexUsuario(){
		$errno = 0;
	}
}

/**
 * Representa un precontrato
 * 
 */
class ComplexPrecontrato {
	public $errno; //int
	
	public $nomAlumno; //string
	public $idAlumno; //int
	
	public $nomDestino; //string
	public $idDestino; //int
	
	public $titulacionAlumno; //string
	//public $nvlAlumno; //string
	
	//public $asignaturas; //ArrayAsignaturasExt
	
	public function ComplexPrecontrato(){
		$errno = 0;
	}
}

/**
 * Representa una lista de precontratos
 * 
 */
class ArrayPrecontratos {
	public $errno; //int
	
	public $precontratos; //Vector<ComplexPrecontrato>
	
	public function ArrayPrecontratos(){
		$errno = 0;
		$precontratos = array();
	}
}
?>