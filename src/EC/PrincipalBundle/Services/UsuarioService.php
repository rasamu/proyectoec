<?php

namespace EC\PrincipalBundle\Services;

use Symfony\Component\Security\Core\Util\SecureRandom;

class UsuarioService {

    function __construct($entityManager) {
			$this->em = $entityManager;
	 }
	 
	 public function setSecurePassword($entity) {
			$entity->setSalt(md5(time()));
			$encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
			$password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
			$entity->setPassword($password);
	 }
	 
	 public function generar_password() {
			$generator = new SecureRandom();
			$random = bin2hex($generator->nextBytes(4));
			return $random;
	 }
	 
	 public function generar_nombre_usuario($nombre) {
	 		$nombre=strtolower($nombre);//Convierte a minusculas
	 		$nombre = preg_replace('/[.*-_;]/', '', $nombre);//Eliminamos caracteres especiales
	 		$vector=preg_split("/[\s,]+/",$nombre);//Divide en elementos de un vector
	 		
			$usuario=$vector[0].'.'.$vector[1];
			if($this->comprobar_nombre_usuario($usuario)){
				return $usuario;
			}else{
				$usuario=$vector[1].'.'.$vector[0];
				if($this->comprobar_nombre_usuario($usuario)){
					return $usuario;
				}else{
					$usuario=$vector[0].'_'.$vector[1];	
					if($this->comprobar_nombre_usuario($usuario)){
						return $usuario;
					}else{
						$usuario=$vector[1].'_'.$vector[0];
						if($this->comprobar_nombre_usuario($usuario)){
							return $usuario;
						}else{
							$generator = new SecureRandom();
							$random = bin2hex($generator->nextBytes(4));
							return $random;
						}
					}
				}
			}
	 }
	 
	 public function comprobar_nombre_usuario($nombre_usuario){
			$query = $this->em->createQuery(
    				'SELECT u
       			FROM ECPrincipalBundle:Usuario u
      			WHERE u.user = :nombre_usuario'
			)->setParameters(array('nombre_usuario' => $nombre_usuario));
			
			try {
    				$comprobacion = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			return 1;
			}	
			return 0;
	 }
}