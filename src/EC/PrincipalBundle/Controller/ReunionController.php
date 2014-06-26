<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\Comunidad;
use EC\PrincipalBundle\Entity\AdminFincas;
use EC\PrincipalBundle\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;


class ReunionController extends Controller
{  
	private function comprobar_comunidad($cif) {
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT c
       			FROM ECPrincipalBundle:Comunidad c
      			WHERE c.cif = :cif and c.administrador = :admin'
			)->setParameters(array('cif' => $cif, 'admin' => $this->getUser()));
			
			try {
    				$comunidad = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}			
    		return $comunidad;	
	 }
 
    /**
	  * @Route("/comunidad/reuniones/{cif}", name="ec_reuniones_comunidad")
	  * @Template("ECPrincipalBundle:Reunion:reuniones_comunidad.html.twig")
	  */
    public function ver_reuniones_comunidadAction($cif=null)
    {
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS') or $this->get('security.context')->isGranted('ROLE_PRESIDENTE') or $this->get('security.context')->isGranted('ROLE_VICEPRESIDENTE')){
    			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    				$comunidad=$this->comprobar_comunidad($cif);
    			}else{
    				$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();	
    			}
			}else{
				$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();
			}
			
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
					/*Buscamos las incidencias de la comunidad*/
    				$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT r
						FROM ECPrincipalBundle:Reunion r
						WHERE r.comunidad IN
						(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.administrador = :admin)'
					)->setParameters(array('admin'=>$this->getUser()));
				
					$reuniones = $query->getResult();	
			}else{
					$reuniones=$comunidad->getReuniones();
			}
			
        	return $this->render('ECPrincipalBundle:Reunion:reuniones_comunidad.html.twig',array('comunidad' =>$comunidad,'reuniones'=>$reuniones));
    }

}