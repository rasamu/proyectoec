<?php

namespace EC\AdminFincasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\ComunidadBundle\Form\Type\ComunidadType;
use EC\ComunidadBundle\Entity\Comunidad;
use EC\AdminFincasBundle\Entity\AdminFincas;
use EC\PrincipalBundle\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use EC\PrincipalBundle\Entity\City;
use EC\PrincipalBundle\Entity\Province;
use Ps\PdfBundle\Annotation\Pdf;

class ComunidadController extends Controller
{  
	private function comprobar_comunidad($cif) {
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT c
       			FROM ECComunidadBundle:Comunidad c
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
	  * @Route("/comunidad/{cif}", name="ec_adminfincas_comunidad")
	  * @Template("ECAdminFincasBundle:Default:comunidad.html.twig")
	  */
    public function comunidadAction($cif)
    {
    		$comunidad=$this->comprobar_comunidad($cif);
			
        	return $this->render('ECAdminFincasBundle:Default:comunidad.html.twig',array('comunidad' =>$comunidad));
    }
	 
	 /**
	  * @Route("/alta/comunidad", name="ec_adminfincas_alta_comunidad")
	  * @Template("ECAdminFincasBundle:Default:alta_comunidad.html.twig")
	  */
    public function alta_comunidadAction(Request $request)
    {	
    		$comunidad=new Comunidad();
    		$form=$this->createForm(new ComunidadType(), $comunidad);
    			
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    				 $cif=$form->get('cif')->getData();
					 $comprobacion=$this->getDoctrine()
        				->getRepository('ECComunidadBundle:Comunidad')
        				->find($cif);
        				
        			 if(!$comprobacion){
							/*Alta nueva*/
    				 		$comunidad->setAdministrador($this->getUser());
    				 		$cuidad=$form->get('city')->getData();
    				 		$comunidad->setCity($cuidad);
    			      	$this->getUser()->addComunidade($comunidad);
							$cuidad->addComunidade($comunidad);    			      	
    			      	
    				 		$em = $this->getDoctrine()->getManager();
   				 		$em->persist($comunidad);
   				 		$em->flush();
   				 		
   				 		$flash=$this->get('translator')->trans('Comunidad registrada con éxito.');
   				 		$this->get('session')->getFlashBag()->add('notice',$flash);
   				 		$this->get('session')->getFlashBag()->add('color','green');
   				 		return $this->redirect($this->generateUrl('ec_adminfincas_listado_comunidades'));
        			 }else{
        			 	if($comprobacion->getAdministrador()){
        			 		/*Ya existe*/
        			 		$flash=$this->get('translator')->trans('Comunidad ya registrada.');
        			 		$this->get('session')->getFlashBag()->add('notice',$flash);
        			 		$this->get('session')->getFlashBag()->add('color','red');
							return $this->redirect($this->generateUrl('ec_adminfincas_listado_comunidades'));
   				 	}else{
							/*Actualizacion*/
							$cuidad=$form->get('city')->getData();
							
							$comprobacion->setCodigo($form->get('codigo')->getData());
							$comprobacion->setNPiscinas($form->get('n_piscinas')->getData());
							$comprobacion->setNPistas($form->get('n_pistas')->getData());
							$comprobacion->setGimnasio($form->get('gimnasio')->getData());
							$comprobacion->setAscensor($form->get('ascensor')->getData());
							$comprobacion->setConserjeria($form->get('conserjeria')->getData());
							$comprobacion->setFechaAlta();
   				 		$comprobacion->setAdministrador($this->getUser());
   				 		$this->getUser()->addComunidade($comprobacion);
   				 		
   				 		$cuidad_old=$comprobacion->getCity();
   				 		$cuidad_old->removeComunidade($comprobacion);
   				 		$comprobacion->setCity($cuidad);
    			      	$cuidad->addComunidade($comprobacion);
    			      	
    			      	$em = $this->getDoctrine()->getManager();
   				 		$em->persist($comprobacion);
   				 		$em->flush();
   				 		
   				 		$flash=$this->get('translator')->trans('Comunidad registrada con éxito.');
   				 		$this->get('session')->getFlashBag()->add('notice',$flash);
   				 		$this->get('session')->getFlashBag()->add('color','green');
							return $this->redirect($this->generateUrl('ec_adminfincas_listado_comunidades'));
   				 	}
        			}
        	}      	
        	return $this->render('ECAdminFincasBundle:Default:alta_comunidad.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    /**
	  * @Route("/listado/comunidades", name="ec_adminfincas_listado_comunidades")
	  * @Template("ECAdminFincasBundle:Default:listado_comunidades.html.twig")
	  */
    public function listado_comunidadesAction()
    {
         $comunidades=$this->getUser()->getComunidades();    
         
        	return $this->render('ECAdminFincasBundle:Default:listado_comunidades.html.twig',array(
        		'comunidades' => $comunidades
        	));
    }
    
   /**
     * @Pdf()
     * @Route("/listado/comunidades/pdf", name="ec_adminfincas_listado_comunidades_pdf")
     */
    public function listado_comunidades_pdfAction()
    {
    	$comunidades=$this->getUser()->getComunidades();    
    	$format = $this->get('request')->get('_format');
    	
    	$filename = "comunidades.pdf";
    	$response=$this->render(sprintf('ECAdminFincasBundle:Default:listado_comunidades_pdf.%s.twig', $format), array(
        		'comunidades' => $comunidades,
    		));
    	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		return $response;
    }
    
    /**
	  * @Route("/listado/comunidades/csv", name="ec_adminfincas_listado_comunidades_csv")
	  * @Template("ECAdminFincasBundle:Default:listado_comunidades_csv.html.twig")
	  */
    public function listado_comunidades_csvAction()
    {
    		$comunidades=$this->getUser()->getComunidades();    
			$filename = "comunidades.csv";
	
			$response = $this->render('ECAdminFincasBundle:Default:listado_comunidades_csv.html.twig', array('comunidades' => $comunidades));
			$response->headers->set('Content-Type', 'text/csv');

			$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	 }
    
    
    /**
	  * @Route("/comunidad/{cif}/editar", name="ec_adminfincas_comunidad_editar")
	  * @Template("ECAdminFincasBundle:Default:editar_comunidad.html.twig")
	  */
 	public function editar_comunidadAction($cif, Request $request)
    {
    		$comunidad=$this->comprobar_comunidad($cif); 
	
			$form = $this ->createFormBuilder($comunidad,array('csrf_protection' => false))
					->add('codigo','text',array('label' => 'Código Despacho'))
    				->add('piscinas','choice',array('label' => 'Piscina','choices'=>array(1 => 'Si', 0 => 'No')))
    				->add('pistas','choice',array('label' => 'Pistas Deportivas','choices'=>array(1 => 'Si', 0 => 'No')))
    				->add('gimnasio','choice',array('choices'=>array(1 => 'Si', 0 => 'No')))
    				->add('ascensor','choice',array('choices'=>array(1 => 'Si', 0 => 'No')))
    				->add('conserjeria','choice',array('label'=>'Conserjería', 'choices'=>array(1 => 'Si', '0' => 'No')))
    				->getForm();
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {	
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($comunidad);
   				 	$em->flush();
    					
   				   $flash=$this->get('translator')->trans('Comunidad modificada con éxito.');    					
						$this->get('session')->getFlashBag()->add('notice',$flash);
        			 	$this->get('session')->getFlashBag()->add('color','green');
						return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_editar',
								array('cif'=>$cif)));
        	}
        	
        	return $this->render('ECAdminFincasBundle:Default:editar_comunidad.html.twig',
        	       		array('form' => $form->createView(),'comunidad' => $comunidad
        	      		));
    }
    
    /**
	  * @Route("/comunidad/{cif}/eliminar", name="ec_adminfincas_comunidad_eliminar")
	  * @Template()
	  */
    public function eliminar_comunidadAction($cif)
    {
    		$comunidad=$this->comprobar_comunidad($cif); 
        	
        	$this->getUser()->removeComunidade($comunidad);
        	$comunidad->setAdministrador();
        	$comunidad->setCodigo(0);
			$em = $this->getDoctrine()->getManager();
   	   $em->persist($comunidad);
   	   $em->flush();        	
   	   
   	   $flash=$this->get('translator')->trans('Comunidad eliminada con éxito.');    					
        	$this->get('session')->getFlashBag()->add('notice',$flash);
        	$this->get('session')->getFlashBag()->add('color','green');
			return $this->redirect($this->generateUrl('ec_adminfincas_listado_comunidades'));
    }

}