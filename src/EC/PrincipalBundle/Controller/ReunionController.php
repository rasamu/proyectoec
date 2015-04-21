<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\Comunidad;
use EC\PrincipalBundle\Entity\AdminFincas;
use EC\PrincipalBundle\Entity\Usuario;
use EC\PrincipalBundle\Entity\Reunion;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

class ReunionController extends Controller
{    
    /**
	  * @Route("/comunidad/reuniones/{cif}", name="ec_reuniones_comunidad")
	  * @Template("ECPrincipalBundle:Reunion:reuniones_comunidad.html.twig")
	  */
    public function ver_reuniones_comunidadAction($cif=null,Request $request)
    {
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS') or $this->get('security.context')->isGranted('ROLE_PRESIDENTE') or $this->get('security.context')->isGranted('ROLE_VICEPRESIDENTE')){
    			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    				$ComprobacionesService=$this->get('comprobaciones_service');
      			$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
    			}else{
    				$comunidad=$this->getUser()->getBloque()->getComunidad();	
    			}
			}else{
				$comunidad=$this->getUser()->getBloque()->getComunidad();
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
					
					$reunion = new Reunion();
    				$form = $this->createFormBuilder($reunion, array('csrf_protection' => false))
    				->add('fecha', 'date', array(
    					'mapped' => false,
    					'read_only'=>true,
    					'format' => 'dd-MM-yyyy',
    					'widget' => 'single_text'))
    				->add('hora', 'time', array(
    					'mapped' => false,
    					'hours'=>range(9,23),
    					'minutes'=>range(0, 59, 30),
    					'input' => 'datetime',
    					'widget' => 'choice'))
        			->add('descripcion','text',array('label'=>'Descripción','max_length'=>25))
        			->add('file','file', array('label'=>'Fichero'))
        			->getForm();

    				$form->handleRequest($request);

    				if ($form->isValid()) {
    					$fecha=$form->get('fecha')->getData();
    					$hora=$form->get('hora')->getData();
    					$hour=$hora->format('H');
    					$minute=$hora->format('i');
    					date_time_set($fecha, $hour, $minute, null);
    					
    					$reunion->setFecha($fecha);
    					$reunion->setComunidad($comunidad);
    					$comunidad->addReunione($reunion);
    					   					
    					$em = $this->getDoctrine()->getManager();
		   			$em->persist($reunion);
		   			$em->persist($comunidad);
         			$em->flush();

						$flash=$this->get('translator')->trans('Reunión registrada con éxito.');
						$this->get('session')->getFlashBag()->add('notice',$flash);
   					$this->get('session')->getFlashBag()->add('color','green');
   					return $this->redirect($this->generateUrl('ec_reuniones_comunidad', array('cif'=>$comunidad->getCif())));
    				}	
			}else{
					$reuniones=$comunidad->getReuniones();
			}
			
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
        		return $this->render('ECPrincipalBundle:Reunion:reuniones_comunidad.html.twig',array('comunidad' =>$comunidad,'reuniones'=>$reuniones,'form' => $form->createView()));
        	}else{
        		return $this->render('ECPrincipalBundle:Reunion:reuniones_comunidad.html.twig',array('comunidad' =>$comunidad,'reuniones'=>$reuniones));
        	}
    }
    
    /**
	  * @Route("/comunidad/reunion/{id}/{cif}", name="ec_ver_reunion_comunidad")
	  * @Template("ECPrincipalBundle:Reunion:reunion_comunidad.html.twig")
	  */
    public function ver_reunion_comunidadAction($id,$cif=null)
    {
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS') or $this->get('security.context')->isGranted('ROLE_PRESIDENTE') or $this->get('security.context')->isGranted('ROLE_VICEPRESIDENTE')){
    			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    				$ComprobacionesService=$this->get('comprobaciones_service');
      			$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
    			}else{
    				$comunidad=$this->getUser()->getBloque()->getComunidad();	
    			}
			}else{
				$comunidad=$this->getUser()->getBloque()->getComunidad();
			}
			
			$ComprobacionesService=$this->get('comprobaciones_service');
      	$reunion=$ComprobacionesService->comprobar_reunion($id);
				
        	return $this->render('ECPrincipalBundle:Reunion:reunion_comunidad.html.twig',array('comunidad' =>$comunidad,'reunion'=>$reunion));
    }
    
    /**
	  * @Route("/reunion/descargar/{id}", name="ec_reunion_descargar")
	  */
	public function descargar_documento_reunionAction($id)
	{
			$ComprobacionesService=$this->get('comprobaciones_service');
      	$reunion=$ComprobacionesService->comprobar_reunion($id);
    		
			$path = $reunion->getWebPath();
         $content = file_get_contents($path);

         $response = new Response();
         $response->headers->set('Content-Type', 'application/pdf');
         $response->headers->set('Content-Disposition', 'attachment;filename="'.'reunion'.'.'.$reunion->getFormat());

         $response->setContent($content);
         return $response;
	}
	
	 /**
	  * @Route("adminfincas/comunidad/{cif}/eliminar/reunion/{id}", name="ec_adminfincas_eliminar_reunion")
	  */
    public function eliminar_reunionAction($cif,$id)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
      	$reunion=$ComprobacionesService->comprobar_reunion($id);
			
			$comunidad->removeReunione($reunion);
    		$em = $this->getDoctrine()->getManager();
    		$em->remove($reunion);
    		$em->persist($comunidad);
			$em->flush();
    			
    		$flash=$this->get('translator')->trans('La reunión ha sido eliminada.');
    		$this->get('session')->getFlashBag()->add('notice',$flash);
   		$this->get('session')->getFlashBag()->add('color','green');
   		return $this->redirect($this->generateUrl('ec_reuniones_comunidad', array('cif'=>$comunidad->getCif())));
    }
}