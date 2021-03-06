<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\Comunidad;
use EC\PrincipalBundle\Entity\Bloque;
use EC\PrincipalBundle\Entity\Csv;
use EC\PrincipalBundle\Entity\AdminFincas;
use Symfony\Component\HttpFoundation\Request;
use Ps\PdfBundle\Annotation\Pdf;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BloqueController extends Controller
{	 
	/**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/listado/bloques", name="ec_adminfincas_comunidad_listado_bloques")
	  * @Template("ECPrincipalBundle:Bloque:listado_bloques.html.twig")
	  */
    public function listado_bloquesAction($id_comunidad)
    {
			$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
			
         $bloques=$comunidad->getBloques();
			
        	return $this->render('ECPrincipalBundle:Bloque:listado_bloques.html.twig',array(
        		'bloques' => $bloques, 'comunidad' =>$comunidad
        	));
    }
    
    /**
     * @Pdf()
     * @Route("/adminfincas/comunidad/{id_comunidad}/listado/bloques/pdf", name="ec_adminfincas_comunidad_listado_bloques_pdf")
     * @Template("ECPrincipalBundle:Bloque:listado_bloques_pdf.html.twig")
     */
    public function listado_bloques_pdfAction($id_comunidad)
    {
    	$ComprobacionesService=$this->get('comprobaciones_service');
      $comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
      	 
    	$bloques=$comunidad->getBloques();    
    	$format = $this->get('request')->get('_format');
    	        
    	$filename = "bloques_".$comunidad->getCodigo().".pdf";
    	$response= $this->render(sprintf('ECPrincipalBundle:Bloque:listado_bloques_pdf.%s.twig', $format), array(
        		'bloques' => $bloques, 'comunidad' => $comunidad
    		));
    		
    	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		return $response;
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/listado/bloques/csv", name="ec_adminfincas_comunidad_listado_bloques_csv")
	  * @Template("ECPrincipalBundle:Bloque:listado_bloques_csv.html.twig")
	  */
    public function listado_bloques_csvAction($id_comunidad) {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
      	
    		$bloques=$comunidad->getBloques();   
    	
			$filename = "bloques_".$comunidad->getCodigo().".csv";
	
			$response = $this->render('ECPrincipalBundle:Bloque:listado_bloques_csv.html.twig', array('bloques' => $bloques));
			$response->headers->set('Content-Type', 'text/csv');

			$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	}
    
    /**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/alta/bloque", name="ec_adminfincas_comunidad_alta_bloque")
	  * @Template("ECPrincipalBundle:Bloque:alta_bloque.html.twig")
	  */
    public function alta_bloqueAction(Request $request, $id_comunidad)
    {
			$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
			
			$bloque = new Bloque();
    		$form = $this ->createFormBuilder($bloque,array('csrf_protection' => false))
    				->add('num','text', array('label' => 'Nº Bloque'))
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
    							$ComprobacionesService=$this->get('comprobaciones_service');
      						$comprobacion_bloque=$ComprobacionesService->comprobar_bloque($comunidad,$row[$headers[0]]);
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
   				 			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_bloques', array('id_comunidad'=>$comunidad->getId())));
   				 		}else{
   				 			$flash=$this->get('translator')->trans('Algunos números de bloques del fichero ya existen, por lo que no se han podido dar de alta todos los bloques.');
   				 			$this->get('session')->getFlashBag()->add('notice',$flash);
   				 			$this->get('session')->getFlashBag()->add('color','green');
   				 			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_bloques', array('id_comunidad'=>$comunidad->getId())));	
   				 		}
            		}else{
            			$flash=$this->get('translator')->trans('Cabeceras del fichero CSV no válidas.');
            			$this->get('session')->getFlashBag()->add('notice',$flash);
   				 		$this->get('session')->getFlashBag()->add('color','red');
   				 		return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('id_comunidad'=>$comunidad->getId())));
            		}				
        		}
        		//Manual
        		if ($form->isValid()) {
    				$ComprobacionesService=$this->get('comprobaciones_service');
      			$comprobacion_bloque=$ComprobacionesService->comprobar_bloque($comunidad,$form->get('num')->getData());
            	if($comprobacion_bloque){
            		$flash=$this->get('translator')->trans('Número de bloque ya registrado.');
            		$this->get('session')->getFlashBag()->add('notice',$flash);
   				 	$this->get('session')->getFlashBag()->add('color','red');
   				 	return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('id_comunidad'=>$comunidad->getId())));
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
   				 	return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_bloques', array('id_comunidad'=>$comunidad->getId())));
        			}
        		}
    	  }
        	
        	return $this->render('ECPrincipalBundle:Bloque:alta_bloque.html.twig',
        	       		array('form' => $form->createView(), 'form_csv' => $form_csv->createView(), 'comunidad'=>$comunidad,
        	      		));
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/bloque/editar/{num}", name="ec_adminfincas_comunidad_editar_bloque")
	  * @Template("ECPrincipalBundle:Bloque:editar_bloque.html.twig")
	  */
    public function editar_bloqueAction($id_comunidad, $num, Request $request) {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
      	
			$ComprobacionesService=$this->get('comprobaciones_service');
      	$bloque=$ComprobacionesService->comprobar_bloque($comunidad,$num);
      	$numero=$bloque->getNum();
    		
			$form = $this ->createFormBuilder($bloque, array('csrf_protection' => false))
    				->add('num','text', array('label' => 'Nº Bloque'))
    				->add('direccion','text', array('label' => 'Dirección'))
    				->getForm();

    		$form->handleRequest($request);
        	if ($form->isValid()) {
    				$ComprobacionesService=$this->get('comprobaciones_service');
      			$comprobacion_bloque=$ComprobacionesService->comprobar_bloque($comunidad,$form->get('num')->getData());
            	if($comprobacion_bloque==$bloque or $comprobacion_bloque==null){
            		$bloque->setNum($form->get('num')->getData());
            		$bloque->setDireccion($form->get('direccion')->getData());
            				     				 
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($bloque);
   				 	$em->flush();
    			
    					$flash=$this->get('translator')->trans('Bloque modificado con éxito.');
    					$this->get('session')->getFlashBag()->add('notice',$flash);
   					$this->get('session')->getFlashBag()->add('color','green');
   					return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_bloques', array('id_comunidad'=>$comunidad->getId())));
            	}else{    		
            		$flash=$this->get('translator')->trans('Número de bloque ya registrado.');
            		$this->get('session')->getFlashBag()->add('notice',$flash);
   				 	$this->get('session')->getFlashBag()->add('color','red');
   				 	return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_editar_bloque', array('id_comunidad'=>$comunidad->getId(),'num'=>$numero)));
        			}
    	  }
        	
        return $this->render('ECPrincipalBundle:Bloque:editar_bloque.html.twig',
        	       		array('form' => $form->createView(), 'comunidad'=>$comunidad, 'bloque'=>$bloque
        	      		));
	}	
    
    /**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/bloque/eliminar/{num}", name="ec_adminfincas_comunidad_eliminar_bloque")
	  * @Template("ECPrincipalBundle:Bloque:listado_bloques.html.twig")
	  */
    public function eliminar_bloqueAction($id_comunidad,$num) {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
      	$bloque=$ComprobacionesService->comprobar_bloque($comunidad,$num);
    		
    		$propietarios=$bloque->getPropietarios();

			$PropietarioService=$this->get('propietario_service');
			foreach($propietarios as $propietario){		
        		$PropietarioService->eliminar_propietario($propietario);
			}	

    		//Eliminamos
    		$comunidad->removeBloque($bloque);
    		$em = $this->getDoctrine()->getManager();
    		$em->remove($bloque);
			$em->flush();
    			
    		$flash=$this->get('translator')->trans('Bloque eliminado con éxito.');
    		$this->get('session')->getFlashBag()->add('notice',$flash);
   		$this->get('session')->getFlashBag()->add('color','green');
   		return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_bloques', array('id_comunidad'=>$comunidad->getId())));
	}	
}