<?php

namespace EC\PrincipalBundle\Services;

class ConsultaService {

    function __construct($entityManager) {
		$this->em = $entityManager;
	 }

    public function comprobar_nuevas_actuaciones($usuario, $incidencia){
	 		$query = $this->em->createQuery(
    				'SELECT c
       			FROM ECPrincipalBundle:ConsultaIncidencia c
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
	 
	 public function comprobar_consulta_documento($propietario, $documento){
	 		$query = $this->em->createQuery(
    				'SELECT c
       			FROM ECPrincipalBundle:ConsultaDocumento c
      			WHERE c.propietario = :propietario and c.documento = :documento'
			)->setParameters(array('propietario' => $propietario, 'documento'=>$documento));
			
			try {
    				$consulta = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			$consulta = null;
			}	
			
			return $consulta;
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
	 
	 public function comprobar_servicio_comunidad($id_comunidad, $id_servicio){
			$query = $this->em->createQuery(
    				'SELECT c
       			FROM ECPrincipalBundle:Comunidad c 
       			JOIN c.servicios s
      			WHERE s.id=:id_servicio and c.id=:id_comunidad'
			)->setParameters(array('id_comunidad' => $id_comunidad, 'id_servicio'=>$id_servicio));
			
			try {
    				$consulta = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			$consulta = null;
			}	
			
			return $consulta;
	 }
	 
	 public function comprobar_valoracion_servicio($id_servicio){
			$query = $this->em->createQuery(
    				'SELECT AVG(v.puntuacion) as media
       			FROM ECPrincipalBundle:Valoracion v 
      			WHERE v.servicio=:id_servicio'
			)->setParameters(array('id_servicio'=>$id_servicio));
			
			try {
    				$consulta = $query->getSingleResult();
    				$media = $consulta['media'];
			} catch (\Doctrine\Orm\NoResultException $e) {
        			$media = null;
			}	
			
			return $media;
	 }
	 
	 public function comprobar_total_valoraciones($id_servicio){
			$query = $this->em->createQuery(
    				'SELECT COUNT(v) as total
       			FROM ECPrincipalBundle:Valoracion v 
      			WHERE v.servicio=:id_servicio'
			)->setParameters(array('id_servicio'=>$id_servicio));
			
			try {
    				$consulta = $query->getSingleResult();
    				$total = $consulta['total'];
			} catch (\Doctrine\Orm\NoResultException $e) {
        			$total = 0;
			}	
			
			return $total;
	 }
}