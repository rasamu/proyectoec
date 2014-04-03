<?php

namespace EC\AdminFincasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\ComunidadBundle\Entity\Comunidad;
use EC\ComunidadBundle\Entity\Bloque;
use EC\AdminFincasBundle\Entity\AdminFincas;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Ps\PdfBundle\Annotation\Pdf;

class BloqueController extends Controller
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
	  * @Route("/comunidad/{cif}/listado/bloques", name="ec_adminfincas_comunidad_listado_bloques")
	  * @Template("ECAdminFincasBundle:Default:comunidad_listado_bloques.html.twig")
	  */
    public function comunidad_listado_bloquesAction($cif)
    {
			$comunidad=$this->comprobar_comunidad($cif); 
			
         $bloques=$comunidad->getBloques();
			
        	return $this->render('ECAdminFincasBundle:Default:comunidad_listado_bloques.html.twig',array(
        		'bloques' => $bloques, 'comunidad' =>$comunidad
        	));
    }
    
    /**
     * @Pdf()
     * @Route("/comunidad/{cif}/listado/bloques/pdf", name="ec_adminfincas_comunidad_listado_bloques_pdf")
     */
    public function comunidad_listado_bloques_pdfAction($cif)
    {
    	$comunidad=$this->comprobar_comunidad($cif); 
    	$bloques=$comunidad->getBloques();    
    	$format = $this->get('request')->get('_format');
    	        
    	$filename = "bloques_".$comunidad->getCif().".pdf";
    	$response= $this->render(sprintf('ECAdminFincasBundle:Default:comunidad_listado_bloques_pdf.%s.twig', $format), array(
        		'bloques' => $bloques, 'comunidad' => $comunidad
    		));
    		
    	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		return $response;
    }
    
    /**
	  * @Route("/comunidad/{cif}/listado/bloques/csv", name="ec_adminfincas_comunidad_listado_bloques")
	  * @Template("ECAdminFincasBundle:Default:comunidad_listado_bloques_csv.html.twig")
	  */
    public function comunidad_listado_bloques_csvAction($cif) {
    		$comunidad=$this->comprobar_comunidad($cif); 
    		$bloques=$comunidad->getBloques();   
    	
			$filename = "bloques_".$comunidad->getCif().".csv";
	
			$response = $this->render('ECAdminFincasBundle:Default:comunidad_listado_bloques_csv.html.twig', array('bloques' => $bloques));
			$response->headers->set('Content-Type', 'text/csv');

			$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	}
    
    /**
	  * @Route("/comunidad/{cif}/alta/bloque", name="ec_adminfincas_comunidad_alta_bloque")
	  * @Template("ECAdminFincasBundle:Default:comunidad_alta_bloque.html.twig")
	  */
    public function comunidad_alta_bloqueAction(Request $request, $cif)
    {
			$comunidad=$this->comprobar_comunidad($cif); 
			
			$bloque = new Bloque();
    		$form = $this ->createFormBuilder($bloque,array('csrf_protection' => false))
    				->add('num','integer', array('label' => 'Número','max_length' =>9))
    				->add('direccion','text', array('label' => 'Dirección'))
    				->getForm();
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
					$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT b
       				FROM ECComunidadBundle:Bloque b
      				WHERE b.num = :num and b.comunidad = :comunidad'
					)->setParameters(array('num' => $form->get('num')->getData(), 'comunidad' => $comunidad));    			
    			
    				 $comprobacion = $query->getResult();
            	
            	if($comprobacion){
            		
            		$this->get('session')->getFlashBag()->add('notice','Número de bloque ya registrado.');
   				 	$this->get('session')->getFlashBag()->add('color','red');
   				 	return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('cif'=>$comunidad->getCif())));
            	}else{    		
            		$bloque->setComunidad($comunidad);
            		$comunidad->addBloque($bloque);
            				     				 
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($bloque);
   				 	$em->flush();
    			
						$this->get('session')->getFlashBag()->add('notice','Registro de bloque realizado con éxito.');
   				 	$this->get('session')->getFlashBag()->add('color','green');
   				 	return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('cif'=>$comunidad->getCif())));
        			}
        	}
        	
        	return $this->render('ECAdminFincasBundle:Default:alta_bloque.html.twig',
        	       		array('form' => $form->createView(), 'comunidad'=>$comunidad,
        	      		));
    }
}