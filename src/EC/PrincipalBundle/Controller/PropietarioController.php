<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\Comunidad;
use EC\PrincipalBundle\Entity\Bloque;
use EC\PrincipalBundle\Entity\Usuario;
use EC\PrincipalBundle\Entity\Propietario;
use EC\PrincipalBundle\Form\Type\PropietarioType;
use EC\PrincipalBundle\Entity\Csv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Ps\PdfBundle\Annotation\Pdf;
use Doctrine\Common\Collections\ArrayCollection;

class PropietarioController extends Controller
{   	 
	/**
	  * @Pdf()
	  * @Route("/comunidad/{cif}/alta/propietario/{_format}", name="ec_adminfincas_comunidad_alta_propietario")
	  * @Template("ECPrincipalBundle:Propietario:alta_propietario.html.twig")
	  */
    public function alta_propietarioAction(Request $request, $cif)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
      	
    		$bloques=$comunidad->getBloques();
    
    		$propietario= new Propietario();
			$form=$this->createForm(new PropietarioType($comunidad),$propietario);
			
			$csv=new Csv();
			$form_csv=$this->createFormBuilder($csv,array('csrf_protection' => false))
					->add('file','file', array('label' => 'Fichero CSV', 'required' => true))
					->getForm();
    		//Comprobamos primero si hay bloques
    		if(count($bloques)!=0){
				if ($this->getRequest()->isMethod('POST')) {
        			$form_csv->bind($this->getRequest());
        			$form->bind($this->getRequest());
        			//Form
        			if ($form->isValid()) {
        				//Comprobamos si ya existe la propiedad
        				$bloque=$form->get('bloque')->getData();
        				$ComprobacionesService=$this->get('comprobaciones_service');
      				$propietario_existente=$ComprobacionesService->comprobar_propiedad($bloque,$propietario->getPropiedad());
        				
						if(!$propietario_existente or $propietario_existente->getRazon()!=$propietario->getRazon()){
								if($propietario_existente){																
									//Eliminamos el antiguo propietario
									$PropietarioService=$this->get('propietario_service');
        							$PropietarioService->eliminar_propietario($propietario_existente);												
								}		
								$propietario->setBloque($bloque);
								$bloque->addPropietario($propietario);				
					
								$ComprobacionesService=$this->get('usuario_service');
      						$password=$ComprobacionesService->generar_password();
      						$nombre_usuario=$ComprobacionesService->generar_nombre_usuario($propietario->getRazon());
      				
								$propietario->setUser($nombre_usuario);
								$propietario->setPassword($password);

								$ComprobacionesService=$this->get('usuario_service');
      						$ComprobacionesService->setSecurePassword($propietario);
      						
								$role=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('2');
								$propietario->setRole($role[0]);
								$role[0]->addUsuario($propietario);
    			
    							$em = $this->getDoctrine()->getManager();
   							$em->persist($bloque);
   							$em->persist($propietario);
   							$em->persist($role[0]);
   							$em->flush();
   							
   							$imprimir="<tr>
   												<td>".$propietario->getBloque()->getNum()."</td>
   												<td>".$propietario->getPropiedad()."</td>
   												<td>".$propietario->getRazon()."</td>
													<td>".$propietario->getUser()."</td>
													<td>".$password."</td>
      										</tr>";
      										
      						$mensaje=$this->get('translator')->trans('Se han dado de alta los siguientes propietarios').':';				
      						$format = $this->get('request')->get('_format');
    							$filename = "nuevos_propietarios_".$comunidad->getCodigo().".pdf";    	        
    							$response=$this->render(sprintf('ECPrincipalBundle:Propietario:comunidad_listado_propietarios_password_pdf.%s.twig',$format), array('imprimir'=>$imprimir,'mensaje'=>$mensaje,'comunidad'=>$comunidad));
    							$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
								return $response;							   						   							  							
   					}else{
   							$flash=$this->get('translator')->trans('Propietario ya registrado.');
   				 			$this->get('session')->getFlashBag()->add('notice',$flash);
   				 			$this->get('session')->getFlashBag()->add('color','red');
   				 			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));
   					}
        			}
        			//Form CSV
        			if ($form_csv->isValid()) {
        				$reader = new \EasyCSV\Reader($csv->getFile());
        				$reader->setDelimiter(';');
        				$headers=$reader->getHeaders();
        				$aux=0;
        				//--Recorremos el fichero csv haciendo todas las comprobaciones--
        				//Comprobamos si las cabeceras del fichero csv son válidas
        				if($headers[0]=='nfinca' && $headers[1]=='piso' && $headers[2]=='razon') {
        					while($row = $reader->getRow()){  			
        						//Comprobamos que existe el bloque
    							$ComprobacionesService=$this->get('comprobaciones_service');
      						$bloque_csv=$ComprobacionesService->comprobar_bloque($comunidad,$row[$headers[0]]);						        				
    							if($bloque_csv){
    								//Comprobamos si ya existe el propietario
      							$propietario_existente=$ComprobacionesService->comprobar_propiedad($bloque_csv,$row[$headers[1]]);
    								if($propietario_existente!=null and $propietario_existente->getRazon()==$row[$headers[2]]){
   				 					$flash=$this->get('translator')->trans('Error: Existen propietarios que ya han sido dados de alta.');
   				 					$this->get('session')->getFlashBag()->add('notice',$flash);
   				 					$this->get('session')->getFlashBag()->add('color','red');
   				 					return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));		
   				 				}
   				 			}else{
   				 				$flash=$this->get('translator')->trans('Error: Existen bloques que no están dados de alta.');
   				 				$this->get('session')->getFlashBag()->add('notice',$flash);
   				 				$this->get('session')->getFlashBag()->add('color','red');
   				 				return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));	
   				 			}
    						}
        				}else{
            			$flash=$this->get('translator')->trans('Cabeceras del fichero CSV no válidas.');
            			$this->get('session')->getFlashBag()->add('notice',$flash);
   				 		$this->get('session')->getFlashBag()->add('color','red');
   				 		return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));
            		}	
            		
            		//Recorremos el fichero dando de alta a todos los propietarios
            		$reader = new \EasyCSV\Reader($csv->getFile());
        				$reader->setDelimiter(';');
        				$headers=$reader->getHeaders();
            		$imprimir="";
        				while($row = $reader->getRow()){ 
        						$ComprobacionesService=$this->get('comprobaciones_service');
      						$bloque_csv=$ComprobacionesService->comprobar_bloque($comunidad,$row[$headers[0]]);	  
        													        				 			
    							$propietario_csv=new Propietario();									
								$propietario_csv->setBloque($bloque_csv);
								$bloque_csv->addPropietario($propietario_csv);
								$propietario_csv->setPropiedad($row[$headers[1]]);							
		
								$propietario_csv->setRazon($row[$headers[2]]);
								
								$ComprobacionesService=$this->get('usuario_service');
      						$password=$ComprobacionesService->generar_password();
      						$nombre_usuario=$ComprobacionesService->generar_nombre_usuario($propietario_csv->getRazon());
      						
								$propietario_csv->setUser($nombre_usuario);
								$propietario_csv->setPassword($password);

								$ComprobacionesService=$this->get('usuario_service');
      						$ComprobacionesService->setSecurePassword($propietario_csv);
      						
								$role=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('2');
								$propietario_csv->setRole($role[0]);
								$role[0]->addUsuario($propietario_csv);
								
								$em = $this->getDoctrine()->getManager();
   				 			$em->persist($bloque_csv);
   				 			$em->persist($propietario_csv);
   				 			$em->persist($role[0]);
   				 			$em->flush();     
   				 			
   				 			$imprimir=$imprimir."<tr>
   												<td>".$propietario_csv->getBloque()->getNum()."</td>
   												<td>".$propietario_csv->getPropiedad()."</td>
   												<td>".$propietario_csv->getRazon()."</td>
													<td>".$propietario_csv->getUser()."</td>
													<td>".$password."</td>
      										</tr>";
   				 	}
   				 	
   				 	$mensaje=$this->get('translator')->trans('Se han dado de alta los siguientes propietarios').':';
   				 	$format = $this->get('request')->get('_format');
    					$filename = "nuevos_propietarios_".$comunidad->getCodigo().".pdf";    	        
    					$response=$this->render(sprintf('ECPrincipalBundle:Propietario:comunidad_listado_propietarios_password_pdf.%s.twig',$format), array('imprimir'=>$imprimir,'mensaje'=>$mensaje,'comunidad'=>$comunidad));
    					$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);				
						return $response;	 					
        			}
        		}
        		return $this->render('ECPrincipalBundle:Propietario:alta_propietario.html.twig',
        	       		array('form' => $form->createView(),'form_csv' => $form_csv->createView(), 'comunidad'=>$comunidad,
        	      		));
        	}else{
        		$flash=$this->get('translator')->trans('Para dar de alta a los propietarios, primero debe dar de alta a los bloques.');
        		$this->get('session')->getFlashBag()->add('notice',$flash);
   			$this->get('session')->getFlashBag()->add('color','red');
   			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_bloque', array('cif'=>$comunidad->getCif())));
        	}
    }
    
    /**
     * @Pdf()
     * @Route("/adminfincas/comunidad/{cif}/propietario/password/{id}", name="ec_adminfincas_comunidad_generar_password_propietario")
     */
    public function comunidad_generar_password_propietario_pdfAction($cif, $id)
    {
    	$ComprobacionesService=$this->get('comprobaciones_service');
      $comunidad=$ComprobacionesService->comprobar_comunidad($cif); 
    	
    	$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
    		'SELECT p
       	FROM ECPrincipalBundle:Propietario p
      	WHERE p.id = :id'
		)->setParameters(array('id' => $id));		
		try {
    		$propietario = $query->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
        	throw new AccessDeniedException();
		}
		
		$ComprobacionesService=$this->get('usuario_service');
      $password_generado=$ComprobacionesService->generar_password();
      						
		$propietario->setPassword($password_generado);
		$ComprobacionesService=$this->get('usuario_service');
      $ComprobacionesService->setSecurePassword($propietario);
   	$em->persist($propietario);
   	$em->flush();     
   	   				 			
    	$imprimir="<tr>
   						<td>".$propietario->getBloque()->getNum()."</td>
   						<td>".$propietario->getPropiedad()."</td>
   						<td>".$propietario->getRazon()."</td>
							<td>".$propietario->getUser()."</td>
							<td>".$password_generado."</td>
      			</tr>";
      									
      $mensaje=$this->get('translator')->trans('Nuevas contraseñas generadas').':';										
      $format = $this->get('request')->get('_format');
    	$filename = "nueva_contraseña_".$propietario->getId()."_".$comunidad->getCodigo().".pdf";    	        
    	$response=$this->render(sprintf('ECPrincipalBundle:Propietario:comunidad_listado_propietarios_password_pdf.%s.twig',$format), array('imprimir'=>$imprimir,'mensaje'=>$mensaje,'comunidad'=>$comunidad));
    	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		return $response;	
    }
    
	/**
	  * @Route("/comunidad/{cif}/propietario/eliminar/{id}", name="ec_adminfincas_comunidad_eliminar_propietario")
	  */
    public function comunidad_eliminar_propietarioAction($cif, $id)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
    		
    		/*Buscamos al propietario*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT p
       			FROM ECPrincipalBundle:Propietario p
      			WHERE p.id = :id'
			)->setParameters(array('id' => $id));
			
			try {
    				$propietario = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}
        	
        	/*Buscamos si tiene incidencias abiertas*/
        	$em = $this->getDoctrine()->getManager();	
			$query = $em->createQuery(
    			'SELECT i FROM ECPrincipalBundle:Incidencia i
    			WHERE i.propietario=:propietario and i.estado!=3'
			)->setParameters(array('propietario'=>$propietario));			
			$incidencias_abiertas = $query->getResult();
        	
        	if($incidencias_abiertas){
        		$flash1=$this->get('translator')->trans('El propietario');
				$flash2=$this->get('translator')->trans('no se ha podido dar de baja. Tiene incidencias no finalizadas.');
				$this->get('session')->getFlashBag()->add('notice',$flash1.' '.$propietario->getRazon().' '.$flash2);
        		$this->get('session')->getFlashBag()->add('color','red');
        	}else{
        		$PropietarioService=$this->get('propietario_service');
        		$PropietarioService->eliminar_propietario($propietario);
        	
        		$flash1=$this->get('translator')->trans('El propietario');
				$flash2=$this->get('translator')->trans('ha sido eliminado.');
				$this->get('session')->getFlashBag()->add('notice',$flash1.' '.$propietario->getRazon().' '.$flash2);
        		$this->get('session')->getFlashBag()->add('color','green');
        	}
        	
    		return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_propietarios', array('cif' => $cif)));
    }    
    
    /**
	  * @Route("/comunidad/{cif}/listado/propietarios", name="ec_adminfincas_comunidad_listado_propietarios")
	  * @Template("ECAdminFincasBundle:Default:comunidad_listado_propietarios.html.twig")
	  */
    public function comunidad_listado_propietariosAction($cif)
    {
			$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
			    		
         $bloques=$comunidad->getBloques();
			
        	return $this->render('ECPrincipalBundle:Propietario:comunidad_listado_propietarios.html.twig',array(
        		'bloques' => $bloques, 'comunidad' =>$comunidad
        	));
    }
    
    /**
     * @Pdf()
     * @Route("/comunidad/{cif}/listado/propietarios/pdf", name="ec_adminfincas_comunidad_listado_propietarios_pdf")
     */
    public function comunidad_listado_propietarios_pdfAction($cif)
    {
    	$ComprobacionesService=$this->get('comprobaciones_service');
      $comunidad=$ComprobacionesService->comprobar_comunidad($cif);
      	
    	$bloques=$comunidad->getBloques();    
    	$format = $this->get('request')->get('_format');
    	
    	$filename = "propietarios_".$comunidad->getCodigo().".pdf";    	        
    	$response=$this->render(sprintf('ECPrincipalBundle:Propietario:comunidad_listado_propietarios_pdf.%s.twig', $format), array(
        		'bloques' => $bloques, 'comunidad' => $comunidad
    		));
    	
    	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		return $response;
    }
    
    /**
	  * @Route("/comunidad/{cif}/listado/propietarios/csv", name="ec_adminfincas_comunidad_listado_propietarios")
	  * @Template("ECAdminFincasBundle:Default:comunidad_listado_propietarios_csv.html.twig")
	  */
    public function comunidad_listado_propietarios_csvAction($cif) {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
      	
    		$bloques=$comunidad->getBloques();   
    	
			$filename = "propietarios_".$comunidad->getCodigo().".csv";
	
			$response = $this->render('ECPrincipalBundle:Propietario:comunidad_listado_propietarios_csv.html.twig', array('bloques' => $bloques));
			$response->headers->set('Content-Type', 'text/csv');

			$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	}
    
    	 /**
	  * @Route("/perfil", name="ec_propietario_perfil")
	  * @Template("ECPrincipalBundle:Propietario:modificacion_datospersonales.html.twig")
	  */
    public function modificacion_perfilAction(Request $request)
    {
    		$propietario = $this->getUser();
    		
    		$form = $this ->createFormBuilder($propietario)
    		    	->setAction($this->generateUrl('ec_propietario_perfil'))
    				->add('razon','text',array('label' => 'Nombre/Razón Social'))
    				->add('telefono','text', array('label' => 'Teléfono','max_length' =>9,'required'=>false))
    				->add('email','email',array('required'=>false))
    				->getForm();
    		
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    			$email=$form->get('email')->getData();
    			
    			if($email!=null){
        			$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT a
       				FROM ECPrincipalBundle:AdminFincas a
      				WHERE a.email = :email'
					)->setParameters(array('email' => $email));    			
    		
    				try{
    					$comprobacion_email=$query->getSingleResult();	
					} catch (\Doctrine\Orm\NoResultException $e) {
						$comprobacion_email=null;
					}
				}else{
					$comprobacion_email=null;	
				}
				
            if($comprobacion_email){
            	$flash=$this->get('translator')->trans('El email proporcionado ya está siendo usado. Por favor, introduzca un email diferente.');
        			$this->get('session')->getFlashBag()->add('notice',$flash);
   				$this->get('session')->getFlashBag()->add('color','red');
   				return $this->redirect($this->generateUrl('ec_propietario_perfil'));
   			}else{						
    				$em = $this->getDoctrine()->getManager();          	
   				$em->persist($propietario);
   				$em->flush();
    			
					$this->get('session')->getFlashBag()->add('notice','Los datos personales han sido actualizados.');
   				$this->get('session')->getFlashBag()->add('color','green');
   				return $this->redirect($this->generateUrl('ec_propietario_perfil'));
   			}				
        	}
        	
        	return $this->render('ECPrincipalBundle:Propietario:modificacion_datospersonales.html.twig',array('form' => $form->createView(),));
    }
}