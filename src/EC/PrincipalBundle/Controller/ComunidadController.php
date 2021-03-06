<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Form\Type\ComunidadType;
use EC\PrincipalBundle\Entity\Comunidad;
use EC\PrincipalBundle\Entity\AdminFincas;
use EC\PrincipalBundle\Entity\Propietario;
use EC\PrincipalBundle\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use EC\PrincipalBundle\Entity\City;
use EC\PrincipalBundle\Entity\Province;
use Ps\PdfBundle\Annotation\Pdf;

class ComunidadController extends Controller
{  	 
	 /**
	  * @Route("/comunidad/tablon/{id_comunidad}", name="ec_tablon_comunidad")
	  * @Template("ECPrincipalBundle:Comunidad:tablon_comunidad.html.twig")
	  */
    public function ver_tablon_comunidadAction($id_comunidad=null)
    {
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS') or $this->get('security.context')->isGranted('ROLE_PRESIDENTE') or $this->get('security.context')->isGranted('ROLE_VICEPRESIDENTE')){
    			/*TABLON GENERAL DE TODAS LAS COMUNIDADES PARA ADMINISTRADOR*/
    			if($id_comunidad==null && $this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    				$comunidad=null;
    				
    				/*Buscamos las incidencias de todas las comunidades del administrador*/
    				$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT i
						FROM ECPrincipalBundle:Incidencia i
						WHERE i.estado!=3 and i.propietario IN
						(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
						(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad IN
						(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.administrador= :admin)) order by i.fecha)'
					)->setParameters(array('admin'=>$this->getUser()));	
							
					$incidencias = $query->getResult();
    			}else{
    				/*TABLON DE UNA COMUNIDAD PARA ADMINISTRADOR, PRESIDENTE O VIDEPRESIDENTE*/
    				if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    					$ComprobacionesService=$this->get('comprobaciones_service');
      				$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
    				}else{
    					$comunidad=$this->getUser()->getBloque()->getComunidad();	
    				}
    		
					/*Buscamos las incidencias de la comunidad no finalizadas*/
    				$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT i
						FROM ECPrincipalBundle:Incidencia i
						WHERE i.estado!=3 and i.propietario IN
						(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
						(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad) order by i.fecha)'
					)->setParameters(array('comunidad'=>$comunidad,));	
							
					$incidencias = $query->getResult();	
				}
			}else{
				/*TABLON DE UNA COMUNIDAD PARA PROPIETARIOS*/
				$comunidad=$this->getUser()->getBloque()->getComunidad();
				$bloque=$this->getUser()->getBloque();
    			$privacidad_bloque_publica=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('2');
        		$privacidad_comunidad_publica=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('3');
        				
    			/*Buscamos las incidencias del propietario privadas y de la comunidad o bloque publicas no finalizadas*/
    			$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT i
					FROM ECPrincipalBundle:Incidencia i
					WHERE i.estado!=3 and ((i.privacidad= :privacidad_comunidad_publica and i.propietario IN
						(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
						(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad)))
					or (i.privacidad= :privacidad_bloque_publica and i.propietario IN
						(SELECT s FROM ECPrincipalBundle:Propietario s WHERE s.bloque= :bloque))
					or (i.propietario= :propietario))'
				)->setParameters(array('comunidad'=>$comunidad,'bloque'=>$bloque,'propietario'=>$this->getUser(),'privacidad_comunidad_publica'=>$privacidad_comunidad_publica,'privacidad_bloque_publica'=>$privacidad_bloque_publica,));
				
				$incidencias = $query->getResult();					
			}
			
			/*PROXIMAS REUNIONES DE TODAS LAS COMUNIDADES*/
			if($id_comunidad==null && $this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
				$fecha_actual=new \DateTime('now');
				$fecha_proximo_mes=new \DateTime('now');
				$fecha_proximo_mes->modify('+1 month');
			
    			$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT r FROM ECPrincipalBundle:Reunion r WHERE (r.fecha>= :fecha_actual and r.fecha< :proximo_mes) and r.comunidad IN
    				(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.administrador= :admin) order by r.fecha'
				)->setParameters(array('admin'=>$this->getUser(),'fecha_actual'=>$fecha_actual,'proximo_mes'=>$fecha_proximo_mes,));	
							
				$proximas_reuniones = $query->getResult();
			}else{
				/*PROXIMAS REUNIONES DE UNA COMUNIDAD*/
				$fecha_actual=new \DateTime('now');
				$fecha_proximo_mes=new \DateTime('now');
				$fecha_proximo_mes->modify('+1 month');
			
    			$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT r
					FROM ECPrincipalBundle:Reunion r
					WHERE r.comunidad=:comunidad and (r.fecha>= :fecha_actual and r.fecha< :proximo_mes) order by r.fecha'
				)->setParameters(array('comunidad'=>$comunidad,'fecha_actual'=>$fecha_actual,'proximo_mes'=>$fecha_proximo_mes,));	
							
				$proximas_reuniones = $query->getResult();	
			}
			
        	return $this->render('ECPrincipalBundle:Comunidad:tablon_comunidad.html.twig',array('comunidad' =>$comunidad,'incidencias'=>$incidencias,'reuniones'=>$proximas_reuniones));
    }
	 
	 /**
	  * @Route("/adminfincas/alta/comunidad", name="ec_adminfincas_alta_comunidad")
	  * @Template("ECPrincipalBundle:Comunidad:alta_comunidad.html.twig")
	  */
    public function alta_comunidadAction(Request $request)
    {	
    		$comunidad=new Comunidad();
    		$form=$this->createForm(new ComunidadType(), $comunidad);
    			
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    				 //Comprobamos el Cif
    				 $cif=$form->get('cif')->getData();
					 $comprobacion=$this->getDoctrine()
        				->getRepository('ECPrincipalBundle:Comunidad')
        				->findByCif($cif);
        				
        		    //Comprobamos el código de despacho
        			 $em = $this->getDoctrine()->getManager();
					 $query = $em->createQuery(
    					'SELECT c
						FROM ECPrincipalBundle:Comunidad c
						WHERE c.codigo= :codigo and c.administrador= :admin'
					 )->setParameters(array('codigo'=>$form->get('codigo')->getData(),'admin'=>$this->getUser()));			
        			 $comprobacion_codigo=$query->getResult();
        				
        			if(!$comprobacion_codigo){
        			 		if(!$comprobacion){
								/*Alta nueva*/
    				 			$comunidad->setAdministrador($this->getUser());
    				 			$cuidad=$form->get('city')->getData();
    				 			$comunidad->setCity($cuidad);
    			      		$this->getUser()->addComunidade($comunidad);
								$cuidad->addComunidade($comunidad);    			      	
    			      	
    				 			$em = $this->getDoctrine()->getManager();
   				 			$em->persist($comunidad);
   				 			$em->persist($cuidad);
   				 			$em->persist($this->getUser());
   				 			$em->flush();
   				 		
   				 			$flash=$this->get('translator')->trans('Comunidad registrada con éxito.');
   				 			$this->get('session')->getFlashBag()->add('notice',$flash);
   				 			$this->get('session')->getFlashBag()->add('color','green');
   				 			return $this->redirect($this->generateUrl('ec_adminfincas_listado_comunidades'));
        			 		}else{
        			 			/*Ya existe*/
        			 			$flash=$this->get('translator')->trans('Comunidad ya registrada.');
        			 			$this->get('session')->getFlashBag()->add('notice',$flash);
        			 			$this->get('session')->getFlashBag()->add('color','red');
								return $this->redirect($this->generateUrl('ec_adminfincas_alta_comunidad'));
        					}
        			}else{
        					$flash=$this->get('translator')->trans('El código de despacho ya está siendo usado por otra comunidad.');
        			 		$this->get('session')->getFlashBag()->add('notice',$flash);
        			 		$this->get('session')->getFlashBag()->add('color','red');
							return $this->redirect($this->generateUrl('ec_adminfincas_alta_comunidad'));
        			}
        	}      	
        	return $this->render('ECPrincipalBundle:Comunidad:alta_comunidad.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    /**
	  * @Route("/adminfincas/listado/comunidades", name="ec_adminfincas_listado_comunidades")
	  * @Template("ECPrincipalBundle:Comunidad:listado_comunidades.html.twig")
	  */
    public function listado_comunidadesAction()
    {
         $comunidades=$this->getUser()->getComunidades();    
         
        	return $this->render('ECPrincipalBundle:Comunidad:listado_comunidades.html.twig',array(
        		'comunidades' => $comunidades
        	));
    }
    
   /**
     * @Pdf()
     * @Route("/adminfincas/listado/comunidades/pdf", name="ec_adminfincas_listado_comunidades_pdf")
     */
    public function listado_comunidades_pdfAction()
    {
    	$comunidades=$this->getUser()->getComunidades();    
    	$format = $this->get('request')->get('_format');
    	
    	$filename = "comunidades.pdf";
    	$response=$this->render(sprintf('ECPrincipalBundle:Comunidad:listado_comunidades_pdf.%s.twig', $format), array(
        		'comunidades' => $comunidades,
    		));
    	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		return $response;
    }
    
    /**
	  * @Route("/adminfincas/listado/comunidades/csv", name="ec_adminfincas_listado_comunidades_csv")
	  * @Template("ECPrincipalBundle:Comunidad:listado_comunidades_csv.html.twig")
	  */
    public function listado_comunidades_csvAction()
    {
    		$comunidades=$this->getUser()->getComunidades();    
			$filename = "comunidades.csv";
	
			$response = $this->render('ECPrincipalBundle:Comunidad:listado_comunidades_csv.html.twig', array('comunidades' => $comunidades));
			$response->headers->set('Content-Type', 'text/csv');

			$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	 }
    
    
    /**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/editar", name="ec_adminfincas_comunidad_editar")
	  * @Template("ECPrincipalBundle:Comunidad:editar_comunidad.html.twig")
	  */
 	public function editar_comunidadAction($id_comunidad, Request $request)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
      	 
    		$codigo_anterior=$comunidad->getCodigo();
	
			$form = $this ->createFormBuilder($comunidad,array('csrf_protection' => false))
					->add('codigo','text',array('label' => 'Código Despacho'))
    				->getForm();
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {	
    				//Comprobamos el código de despacho
        			 $em = $this->getDoctrine()->getManager();
					 $query = $em->createQuery(
    					'SELECT c
						FROM ECPrincipalBundle:Comunidad c
						WHERE c.codigo= :codigo and c.administrador= :admin'
					 )->setParameters(array('codigo'=>$form->get('codigo')->getData(),'admin'=>$this->getUser()));			
        			 $comprobacion_codigo=$query->getResult();
        				
        			if($codigo_anterior==$form->get('codigo')->getData() or !$comprobacion_codigo){
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($comunidad);
   				 	$em->flush();
    					
   				   $flash=$this->get('translator')->trans('Comunidad modificada con éxito.');    					
						$this->get('session')->getFlashBag()->add('notice',$flash);
        			 	$this->get('session')->getFlashBag()->add('color','green');
						return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_editar',array('id_comunidad'=>$id_comunidad)));
					}else{
						$flash=$this->get('translator')->trans('El código de despacho ya está siendo usado por otra comunidad.');
        			 	$this->get('session')->getFlashBag()->add('notice',$flash);
        			 	$this->get('session')->getFlashBag()->add('color','red');
						return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_editar',array('id_comunidad'=>$id_comunidad)));
					}
        	}
        	
        	return $this->render('ECPrincipalBundle:Comunidad:editar_comunidad.html.twig',
        	       		array('form' => $form->createView(),'comunidad' => $comunidad
        	      		));
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/eliminar", name="ec_adminfincas_comunidad_eliminar")
	  * @Template()
	  */
    public function eliminar_comunidadAction($id_comunidad)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
    		
    		$bloques=$comunidad->getBloques();
			foreach($bloques as $bloque){
				$propietarios=$bloque->getPropietarios();
				$PropietarioService=$this->get('propietario_service');
				foreach($propietarios as $propietario){						
        			$PropietarioService->eliminar_propietario($propietario);
				}		
			}    		
        	
        	$this->getUser()->removeComunidade($comunidad);        
        	
        	//Eliminamos la comunidad, (Documentos, reuniones y bloques con cascade={"remove"})
			$em = $this->getDoctrine()->getManager();
   	   $em->remove($comunidad);
   	   $em->persist($this->getUser());
   	   $em->flush();        	
   	   
   	   $flash=$this->get('translator')->trans('Comunidad eliminada con éxito.');    					
        	$this->get('session')->getFlashBag()->add('notice',$flash);
        	$this->get('session')->getFlashBag()->add('color','green');
			return $this->redirect($this->generateUrl('ec_adminfincas_listado_comunidades'));
    }

	 /**
	  * @Route("/adminfincas/estadisticas/comunidades", name="ec_adminfincas_estadisticas_comunidades")
	  * @Template("ECPrincipalBundle:Comunidad:estadisticas_comunidades.html.twig")
	  */
    public function estadisticasComunidadesAction()
    {   			
			/*Comunidades del administrador*/
    		$em = $this->getDoctrine()->getManager();	
			$query = $em->createQuery(
    			'SELECT c.codigo as codigo FROM ECPrincipalBundle:Comunidad c WHERE c.administrador= :admin order by codigo'
			)->setParameters(array('admin'=>$this->getUser()));			
			$comunidades = $query->getResult();
    		
    		$totales_propietarios=array();
    		$totales_incidencias=array();
    		$totales_anuncios=array();
    		
    		foreach($comunidades as $comunidad){
    				/*TOTALES DE PROPIETARIOS*/
    				$em = $this->getDoctrine()->getManager();	
					$query = $em->createQuery(
    					'SELECT COUNT(u) as total FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
    					(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad IN
    					(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.codigo= :comunidad and c.administrador= :admin))'
					)->setParameters(array('admin'=>$this->getUser(),'comunidad'=>$comunidad['codigo']));							
					$propietarios = $query->getSingleResult();
					
					if($propietarios==null){
						$totales_propietarios[]=0;
					}else{
						$totales_propietarios[]=$propietarios;
					}
					
					/*TOTALES DE INCIDENCIAS*/
					$em = $this->getDoctrine()->getManager();	
					$query = $em->createQuery(
    					'SELECT COUNT(i) as total FROM ECPrincipalBundle:Incidencia i WHERE i.propietario IN
    					(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
    					(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad IN
    					(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.codigo= :comunidad and c.administrador= :admin)))'
					)->setParameters(array('admin'=>$this->getUser(),'comunidad'=>$comunidad['codigo']));							
					$incidencias = $query->getSingleResult();
					
					if($incidencias==null){
						$totales_incidencias[]=0;
					}else{
						$totales_incidencias[]=$incidencias;
					}
					
					/*TOTALES DE ANUNCIOS*/
					$em = $this->getDoctrine()->getManager();	
					$query = $em->createQuery(
    					'SELECT COUNT(a) as total FROM ECPrincipalBundle:Anuncio a WHERE a.comunidad IN
    					(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.codigo= :comunidad and c.administrador= :admin)'
					)->setParameters(array('admin'=>$this->getUser(),'comunidad'=>$comunidad['codigo']));							
					$anuncios = $query->getSingleResult();
					
					if($anuncios==null){
						$totales_anuncios[]=0;
					}else{
						$totales_anuncios[]=$anuncios;
					}
    		}
    		
    		return $this->render('ECPrincipalBundle:Comunidad:estadisticas_comunidades.html.twig',
    				array('comunidades'=>$comunidades,'totales_propietarios'=>$totales_propietarios,'totales_incidencias'=>$totales_incidencias,'totales_anuncios'=>$totales_anuncios,
    				));	
    }
}