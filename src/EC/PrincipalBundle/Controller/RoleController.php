<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\Comunidad;
use EC\PrincipalBundle\Entity\Bloque;
use EC\PrincipalBundle\Entity\Usuario;
use EC\PrincipalBundle\Entity\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Util\SecureRandom;

class RoleController extends Controller
{   
    /**
	  * @Route("/adminfincas/comunidad/{cif}/nombrarpresidente/{id}", name="ec_adminfincas_nombrar_presidente")
	  * @Template()
	  */
    public function nombrar_presidenteAction($cif, $id){
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
    		$role_presidente=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('3');
						
    		/*Buscamos y eliminamos Presidente anterior*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT u
					FROM ECPrincipalBundle:Propietario u
					WHERE u.role = :role_presidente and u.bloque IN
					(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad)'
			)->setParameters(array('comunidad'=>$comunidad,'role_presidente'=>$role_presidente[0],));
			
			try {
				$propietario = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$propietario = null;
			}
			
			if($propietario){
				$role_vecino=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('2');
				    		
				$role_presidente[0]->removeUsuario($propietario);
				$role_vecino[0]->addUsuario($propietario);
				$propietario->setRole($role_vecino[0]);
				
				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($propietario);
   	 		$em->persist($role_vecino[0]);
   	 		$em->persist($role_presidente[0]);
   	   	$em->flush();  
			}
			
			/*Buscamos y nombramos al nuevo presidente*/
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT u
       			FROM ECPrincipalBundle:Propietario u
      			WHERE u.id = :id'
			)->setParameters(array('id' => $id,));
			
			try {
				$presidente = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$presidente = null;
			}
			
			if($presidente){
				$presidente->setRole($role_presidente[0]);
				$role_presidente[0]->addUsuario($presidente);
				
				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($presidente);
   	 		$em->persist($role_presidente[0]);
   	   	$em->flush();  
			}
			
			$flash=$this->get('translator')->trans('ha sido nombrado nuevo Presidente.');
			$this->get('session')->getFlashBag()->add('notice',$presidente->getRazon().' '.$flash);
        	$this->get('session')->getFlashBag()->add('color','green');
			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_propietarios', array('cif' => $cif)));
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{cif}/nombrarvicepresidente/{id}", name="ec_adminfincas_nombrar_vicepresidente")
	  * @Template()
	  */
    public function nombrar_vicepresidenteAction($cif, $id){
    	$ComprobacionesService=$this->get('comprobaciones_service');
      $comunidad=$ComprobacionesService->comprobar_comunidad($cif);
    	$bloques=$comunidad->getBloques();
    	$role_vicepresidente=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('4');
    				
    	/*Buscamos y eliminamos Vicepresidente anterior*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT u
					FROM ECPrincipalBundle:Propietario u
					WHERE u.role = :role and u.bloque IN
					(SELECT b.id FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad)'
			)->setParameters(array('comunidad'=>$comunidad,'role'=>$role_vicepresidente[0],));
			
			try {
				$propietario = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$propietario = null;
			}
			
			if($propietario){
				$role_vecino=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('2');
				
				$role_vicepresidente[0]->removeUsuario($propietario);
				$role_vecino[0]->addUsuario($propietario);
				$propietario->setRole($role_vecino[0]);
				
				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($propietario);
   	 		$em->persist($role_vecino[0]);
   	 		$em->persist($role_vicepresidente[0]);
   	   	$em->flush();  
			}
			
			/*Buscamos y nombramos al nuevo Vicepresidente*/
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT u
       			FROM ECPrincipalBundle:Propietario u
      			WHERE u.id = :id'
			)->setParameters(array('id' => $id,));
			
			try {
				$vicepresidente = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$vicepresidente = null;
			}
			
			if($vicepresidente){
				$vicepresidente->setRole($role_vicepresidente[0]);
				$role_vicepresidente[0]->addUsuario($vicepresidente);

				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($vicepresidente);
   	 		$em->persist($role_vicepresidente[0]);
   	   	$em->flush();  
			}
			$flash=$this->get('translator')->trans('ha sido nombrado nuevo Vicepresidente.');
			$this->get('session')->getFlashBag()->add('notice',$vicepresidente->getRazon().' '.$flash);
        	$this->get('session')->getFlashBag()->add('color','green');
			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_propietarios', array('cif' => $cif)));
    }
}