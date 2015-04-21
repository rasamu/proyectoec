<?php

namespace EC\PrincipalBundle\Services;

class ConsultaService {

    function __construct($entityManager) {
		$this->em = $entityManager;
	 }

    public function comprobar_nuevas_actuaciones($usuario, $incidencia){
	 		$query = $this->em->createQuery(
    				'SELECT c
       			FROM ECPrincipalBundle:Consulta c
      			WHERE c.usuario = :usuario and c.incidencia = :incidencia'
			)->setParameters(array('usuario' => $usuario, 'incidencia'=>$incidencia));
			
			try {
    				$consulta = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			$consulta = null;
			}	
			
			if($consulta!=null){
				$fecha_ultima_consulta=$consulta->getFecha();
				$query = $this->em->createQuery(
    				'SELECT COUNT(a)
       			FROM ECPrincipalBundle:Actuacion a
      			WHERE a.incidencia = :incidencia and a.fecha > :fecha_ultima_consulta'
				)->setParameters(array('incidencia'=>$incidencia, 'fecha_ultima_consulta'=>$fecha_ultima_consulta));
			
				try {
					$total=$query->getSingleResult();
    				$total_nuevas_actuaciones = $total[1];
				} catch (\Doctrine\Orm\NoResultException $e) {
        			$total_nuevas_actuaciones = 0;
				}			
			}else{
				$total_nuevas_actuaciones=null;	
			}
			
			return $total_nuevas_actuaciones;	
	 }
	 
	 public function consulta_imagenes_anuncio($id_anuncio){
	 		$query = $this->em->createQuery(
    				'SELECT a
       			FROM ECPrincipalBundle:Anuncio a
      			WHERE a.id = :id'
			)->setParameters(array('id' => $id_anuncio));
			
			try {
    				$anuncio = $query->getSingleResult();
    				return $anuncio->getImagenes();
			} catch (\Doctrine\Orm\NoResultException $e) {
					return null;
			}	
	 }
}