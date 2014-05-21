<?php

namespace EC\AdminFincasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\ComunidadBundle\Entity\Comunidad;
use EC\ComunidadBundle\Entity\Bloque;
use EC\PrincipalBundle\Entity\Csv;
use EC\AdminFincasBundle\Entity\AdminFincas;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Ps\PdfBundle\Annotation\Pdf;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
	 
	 private function comprobar_bloque($comunidad,$num) {
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT b
       			FROM ECComunidadBundle:Bloque b
      			WHERE b.num = :num and b.comunidad = :comunidad'
			)->setParameters(array('num' => $num, 'comunidad' => $comunidad));    			
    		
    		try {
    				$bloque = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			return null;
			}			
    		return $bloque;
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
    				->add('num','integer', array('label' => 'Nº Bloque', 'attr' => array('min' => 9, 'max'=> 9)))
    				->add('direccion','text', array('label' => 'Dirección'))
    				->getForm();
    				
    		$csv=new Csv();
			$form_csv=$this->createFormBuilder($csv,array('csrf_protection' => false))
					->add('file','file', array('label' => 'Fichero CSV', 'required' => true))
					->getForm();
					
		  if ($this->getRequest()->isMethod('POST')) {
        		$form_csv->bind($this->getRequest());
        		$form->bind($this->getRequest());
        		//CSV
        		if ($form_csv->isValid()) {
        				$reader = new \EasyCSV\Reader($csv->getFile());
        				$reader->setDelimiter(';');
        				$headers=$reader->getHeaders();
        				$aux=0;
        				if($headers[0]=='numbloque' && $headers[1]=='domifinca') {
        					while($row = $reader->getRow()){  			
    							$comprobacion_bloque = $this->comprobar_bloque($comunidad,$row[$headers[0]]);
    							if($comprobacion_bloque){
    								$aux=1;
    							}else{
									$bloque=new Bloque();
									$bloque->setNum($row[$headers[0]]);
									$bloque->setDireccion($row[$headers[1]]);
									$bloque->setComunidad($comunidad);
									$comunidad->addBloque($bloque);
								
									$em = $this->getDoctrine()->getManager();
   				 				$em->persist($bloque);
   				 				$em->persist($comunidad);
   				 				$em->flush();
   				 			}	
    						}
    						if($aux==0){
    							$flash=$this->get('translator')->trans('Registro de bloques realizado con éxito.');
    							$this->get('session')->getFlashBag()->add('notice',$flash);
   				 			$this->get('session')->getFlashBag()->add('color','green');
   				 			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('cif'=>$comunidad->getCif())));
   				 		}else{
   				 			$flash=$this->get('translator')->trans('Algunos números de bloques del fichero ya existen, por lo que no se han podido dar de alta todos los bloques.');
   				 			$this->get('session')->getFlashBag()->add('notice',$flash);
   				 			$this->get('session')->getFlashBag()->add('color','red');
   				 			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('cif'=>$comunidad->getCif())));	
   				 		}
            		}else{
            			$flash=$this->get('translator')->trans('Cabeceras del fichero CSV no válidas.');
            			$this->get('session')->getFlashBag()->add('notice',$flash);
   				 		$this->get('session')->getFlashBag()->add('color','red');
   				 		return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('cif'=>$comunidad->getCif())));
            		}				
        		}
        		//Manual
        		if ($form->isValid()) {
    				 $comprobacion_bloque = $this->comprobar_bloque($comunidad,$form->get('num')->getData());   	
            	if($comprobacion_bloque){
            		$flash=$this->get('translator')->trans('Número de bloque ya registrado.');
            		$this->get('session')->getFlashBag()->add('notice',$flash);
   				 	$this->get('session')->getFlashBag()->add('color','red');
   				 	return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('cif'=>$comunidad->getCif())));
            	}else{    		
            		$bloque->setComunidad($comunidad);
            		$comunidad->addBloque($bloque);
            				     				 
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($bloque);
   				 	$em->persist($comunidad);
   				 	$em->flush();
    			
    					$flash=$this->get('translator')->trans('Registro de bloque realizado con éxito.');
						$this->get('session')->getFlashBag()->add('notice',$flash);
   				 	$this->get('session')->getFlashBag()->add('color','green');
   				 	return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('cif'=>$comunidad->getCif())));
        			}
        		}
    	  }
        	
        	return $this->render('ECAdminFincasBundle:Default:alta_bloque.html.twig',
        	       		array('form' => $form->createView(), 'form_csv' => $form_csv->createView(), 'comunidad'=>$comunidad,
        	      		));
    }
    
    /**
	  * @Route("/comunidad/{cif}/bloque/eliminar/{num}", name="ec_adminfincas_comunidad_eliminar_bloque")
	  * @Template("ECAdminFincasBundle:Default:comunidad_listado_bloques.html.twig")
	  */
    public function comunidad_eliminar_bloqueAction($cif,$num) {
    		$comunidad=$this->comprobar_comunidad($cif); 
			$bloque=$this->comprobar_bloque($cif,$num);
    		$propiedades=$bloque->getPropiedades();
    		if(count($propiedades)!=0){
    			$flash=$this->get('translator')->trans('El bloque tiene propietarios registrados. Debe eliminar los propietarios primero.');
    			$this->get('session')->getFlashBag()->add('notice',$flash);
   			$this->get('session')->getFlashBag()->add('color','red');
   			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_bloques', array('cif'=>$comunidad->getCif())));
    		}else{
    			//Eliminamos
    			$comunidad->removeBloque($bloque);
    			$em = $this->getDoctrine()->getManager();
    			$em->remove($bloque);
				$em->flush();
    			
    			$flash=$this->get('translator')->trans('Bloque eliminado con éxito.');
    			$this->get('session')->getFlashBag()->add('notice',$flash);
   			$this->get('session')->getFlashBag()->add('color','green');
   			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_bloques', array('cif'=>$comunidad->getCif())));
    		}
	}
	
}