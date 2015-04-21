<?php

namespace EC\PrincipalBundle\Services;

class PropietarioService {

    function __construct($entityManager) {
		$this->em = $entityManager;
	 }

    public function eliminar_propietario($propietario){
	 		$role=$propietario->getRole();
	 		$bloque=$propietario->getBloque();
			$role->removeUsuario($propietario);
			$bloque->removePropietario($propietario); 	
			
		   $this->eliminar_actuaciones($propietario);	
	 		
	 		//Incidencias con sus actuaciones, logs y anuncios con sus imagenes se eliminan automaticamente con cascade={"remove"}
	 		$this->em->persist($role);
			$this->em->persist($bloque);
    		$this->em->remove($propietario);
    		$this->em->flush();	
	 }
	 
	 //Elimina las actuaciones de un propietario dado en cualquier incidencia
	 public function eliminar_actuaciones($propietario) {
  			$actuaciones=$propietario->getActuaciones();
  			foreach($actuaciones as $actuacion){
  					$actuacion->setUsuario();//Ponemos a null
  					$propietario->removeActuacione($actuacion);
  					
   				$this->em->persist($actuacion);
   				$this->em->persist($propietario);
    				$this->em->flush();
  			}	
	 }
}