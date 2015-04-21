<?php

namespace EC\PrincipalBundle\Services;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;

class ComprobacionesService {

    function __construct($entityManager, $securityContext, $doctrine) {
		$this->em = $entityManager;
		$this->user = $securityContext->getToken()->getUser();
		$this->context = $securityContext;
		$this->doctrine = $doctrine;
	 }
	 
	 public function comprobar_anuncio($id){
			$query = $this->em->createQuery(
    				'SELECT a
       			FROM ECPrincipalBundle:Anuncio a
      			WHERE a.id = :id'
			)->setParameters(array('id' => $id));
			
			try {
    				$anuncio = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}	

    		return $anuncio;
	 }
	 
	 public function comprobar_comunidad($cif) {
			$query = $this->em->createQuery(
    				'SELECT c
       			FROM ECPrincipalBundle:Comunidad c
      			WHERE c.cif = :cif and c.administrador = :admin'
			)->setParameters(array('cif' => $cif, 'admin' => $this->user));
			
			try {
    				$comunidad = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}			
    		return $comunidad;	
	 }
	 
	 public function comprobar_bloque($comunidad,$num) {
			$query = $this->em->createQuery(
    				'SELECT b
       			FROM ECPrincipalBundle:Bloque b
      			WHERE b.num = :num and b.comunidad = :comunidad'
			)->setParameters(array('num' => $num, 'comunidad' => $comunidad));    			
    		
    		try {
    				$bloque = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			return null;
			}			
    		return $bloque;
	 } 
	 
	 public function comprobar_propiedad($bloque,$propiedad) {
			$query = $this->em->createQuery(
    				'SELECT p FROM ECPrincipalBundle:Propietario p WHERE p.propiedad = :propiedad and p.bloque IN
      			(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.id=:id)'
			)->setParameters(array('propiedad'=>$propiedad,'id'=>$bloque->getId()));    			
    		
    		try{
    			$comprobacion=$query->getSingleResult();	
			} catch (\Doctrine\Orm\NoResultException $e) {
				return null;
			}
			return $comprobacion;
	 }
	 
	 public function comprobar_documento($id){
			$query = $this->em->createQuery(
    				'SELECT d
       			FROM ECPrincipalBundle:Documento d
      			WHERE d.id = :id'
			)->setParameters(array('id' => $id));
			
			try {
    				$documento = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}	
			$aux=$documento->getComunidad();
			
    		if($this->context->isGranted('ROLE_ADMINFINCAS')){
    			$comunidad=$this->comprobar_comunidad($aux->getCif());
    		}else{
    			$comunidad=$this->user->getBloque()->getComunidad();	
    			if($comunidad!=$aux){
    				throw new AccessDeniedException();
    			}
    		}
    		
    		return $documento;
	 }
	 
	 public function comprobar_reunion($id){
			$query = $this->em->createQuery(
    				'SELECT r
       			FROM ECPrincipalBundle:Reunion r
      			WHERE r.id = :id'
			)->setParameters(array('id' => $id));
			
			try {
    				$reunion = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}	
			$aux=$reunion->getComunidad();
			
    		if($this->context->isGranted('ROLE_ADMINFINCAS')){
    			$comunidad=$this->comprobar_comunidad($aux->getCif());
    		}else{
    			$comunidad=$this->user->getBloque()->getComunidad();	
    			if($comunidad!=$aux){
    				throw new AccessDeniedException();
    			}
    		}	
    		return $reunion;
	 }
	 
	 public function comprobar_incidencia($id) {
			$query = $this->em->createQuery(
    				'SELECT i
       			FROM ECPrincipalBundle:Incidencia i
      			WHERE i.id = :id'
			)->setParameters(array('id' => $id,));
			
			try {
    				$incidencia = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}
			
			//Comprobamos que la incidencia pertenezca a un propietario de una comunidad cuyo administrador de fincas sea el usuario activo
			if($this->context->isGranted('ROLE_ADMINFINCAS')){
					$admin=$incidencia->getPropietario()->getBloque()->getComunidad()->getAdministrador();
					if($admin!=$this->user){
						throw new AccessDeniedException();
					}
			}else{
				//Comprobamos que el presidente o vicepresidente pertenezca a la misma comunidad de la incidencia
				if($this->context->isGranted('ROLE_PRESIDENTE') or $this->context->isGranted('ROLE_VICEPRESIDENTE')){
					$comunidad=$incidencia->getPropietario()->getBloque()->getComunidad();
					if($comunidad!=$this->user->getBloque()->getComunidad()){
						throw new AccessDeniedException();
					}
				}else{
					//Comprobamos que la incidencia pertenezca al vecino activo o sea pública en el bloque o comunidad
					if($this->context->isGranted('ROLE_VECINO')){
						$bloque=$this->user->getBloque();
						$comunidad=$this->user->getBloque()->getComunidad();
						$propietario=$this->user;
						
						$privado_propietario=$this->doctrine->getRepository('ECPrincipalBundle:Privacidad')->findById('1');
						$privado_bloque=$this->doctrine->getRepository('ECPrincipalBundle:Privacidad')->findById('2');
        				$privado_comunidad=$this->doctrine->getRepository('ECPrincipalBundle:Privacidad')->findById('3');
        				
        				$propietario_incidencia=$incidencia->getPropietario();
        				$bloque_incidencia=$propietario_incidencia->getBloque();
        				$comunidad_incidencia=$bloque_incidencia->getComunidad();
        				$privacidad_incidencia=$incidencia->getPrivacidad();
        				
						if(($comunidad_incidencia!=$comunidad) or //Es de otra comunidad
							($propietario_incidencia!=$propietario and $privacidad_incidencia==$privado_propietario[0]) or //Es de otro propietario y es privado
							($bloque_incidencia!=$bloque and $privacidad_incidencia!=$privado_comunidad[0]) or
							($bloque_incidencia==$bloque and ($privacidad_incidencia==$privado_propietario[0] and $propietario_incidencia!=$propietario)))
							{ 
							throw new AccessDeniedException();
						}
					}else{
							throw new AccessDeniedException();
					}
				}
			}					
    		return $incidencia;	
	 }
	 
	 public function comprobar_consulta($usuario_id, $incidencia_id) {
			$query = $this->em->createQuery(
    				'SELECT c
       			FROM ECPrincipalBundle:Consulta c
      			WHERE c.usuario = :id_usuario and c.incidencia = :id_incidencia'
			)->setParameters(array('id_usuario' => $usuario_id, 'id_incidencia' => $incidencia_id));
			
			try {
    				$consulta = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			$consulta = null;
			}		
    		return $consulta;	
	 }
}