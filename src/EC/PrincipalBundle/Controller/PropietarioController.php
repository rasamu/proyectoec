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
use EC\PrincipalBundle\Entity\Propiedad;
use EC\PrincipalBundle\Entity\Csv;
use EC\PrincipalBundle\Form\Type\PropiedadType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Ps\PdfBundle\Annotation\Pdf;
use Doctrine\Common\Collections\ArrayCollection;

class PropietarioController extends Controller
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
	 
	 private function comprobar_bloque($comunidad,$num) {
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT b
       			FROM ECPrincipalBundle:Bloque b
      			WHERE b.num = :num and b.comunidad = :comunidad'
			)->setParameters(array('num' => $num, 'comunidad' => $comunidad));    			
    		
    		try{
    			$bloque=$query->getSingleResult();	
			} catch (\Doctrine\Orm\NoResultException $e) {
				return null;
			}
			return $bloque;
	 }
	 
	 private function comprobar_propiedad($bloque,$piso) {
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT p FROM ECPrincipalBundle:Propiedad p WHERE p.piso = :piso and p.bloque IN
      			(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.id=:id)'
			)->setParameters(array('piso'=>$piso,'id'=>$bloque->getId()));    			
    		
    		try{
    			$propiedad=$query->getSingleResult();	
			} catch (\Doctrine\Orm\NoResultException $e) {
				return null;
			}
			return $propiedad;
	 }
	 
	 private function eliminar_propiedad($propiedad){
	 		$bloque=$propiedad->getBloque();
	 		$bloque->removePropiedade($propiedad);
	 		$propietario=$propiedad->getPropietario();
	 		
			$em = $this->getDoctrine()->getEntityManager();
			if($propietario){
				$propietario->setPropiedad();
				$em->persist($propietario);	
			}
			$em->persist($bloque);
    		$em->remove($propiedad);
    		$em->flush();	
	 }
	 
	 private function eliminar_propietario($propietario){
	 		$propiedad=$propietario->getPropiedad();
	 		$role=$propietario->getRole();
			$role->removeUsuario($propietario);	 		
	 		
	 		$this->eliminar_actuaciones($propietario);
	 		$this->eliminar_incidencias($propietario);
	 		$this->eliminar_logs($propietario);
	 		
	 		$em = $this->getDoctrine()->getEntityManager();
	 		if($propiedad){
	 			$propiedad->setPropietario();	
	 			$em->persist($propiedad);
	 		}
	 		$em->persist($role);
    		$em->remove($propietario);
    		$em->flush();	
	 }
	 
	 //Elimina los logs de un propietario
	 private function eliminar_logs($propietario){
	 		$logs=$propietario->getLogs();
  			foreach($logs as $log){
  					$propietario->removeLog($log);
  					
  					$em = $this->getDoctrine()->getEntityManager();
   				$em->persist($propietario);
   				$em->remove($log);
    				$em->flush();
  			}
	 }
	 
	 //Elimina las actuaciones de un propietario dado en cualquier incidencia
	 private function eliminar_actuaciones($propietario) {
  			$actuaciones=$propietario->getActuaciones();
  			foreach($actuaciones as $actuacion){
  					$actuacion->setUsuario();//Ponemos a null
  					$propietario->removeActuacione($actuacion);
  					$em = $this->getDoctrine()->getEntityManager();
  					
   				$em->persist($actuacion);
   				$em->persist($propietario);
    				$em->flush();
  			}	
	 }
	 
	 //Elimina las incidencias, y sus respectivas actuaciones, de un propietario dado
	 private function eliminar_incidencias($propietario) {
  			$incidencias=$propietario->getIncidencias();
  			
  			foreach($incidencias as $incidencia){
  				$categoria=$incidencia->getCategoria();
  				$categoria->removeIncidencia($incidencia);
  				$actuaciones=$incidencia->getActuaciones();
  				
  				foreach($actuaciones as $actuacion){
  					$propietario_actuacion=$actuacion->getUsuario();
  					 $em2 = $this->getDoctrine()->getEntityManager();
  					if($propietario_actuacion){
  						$propietario_actuacion->removeActuacione($actuacion);	
  						$em2->persist($propietario_actuacion);
  					}	 					
  					$incidencia->removeActuacione($actuacion);
  					$em2->persist($incidencia);
  					$em2->remove($actuacion);
  					$em2->flush();
  				}
  				
  				$em = $this->getDoctrine()->getEntityManager();
  				$em2->persist($categoria);
   			$em->remove($incidencia);
    			$em->flush();	
    		}	
	 }
	 
	 private function setSecurePassword($entity) {
			$entity->setSalt(md5(time()));
			$encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
			$password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
			$entity->setPassword($password);
	 }
	 
	 private function generar_nombre_usuario($nombre) {
	 		$nombre=strtolower($nombre);//Convierte a minusculas
	 		$nombre = preg_replace('/[.*-_;]/', '', $nombre);//Eliminamos caracteres especiales
	 		$vector=preg_split("/[\s,]+/",$nombre);//Divide en elementos de un vector
	 		
			$usuario=$vector[0].'.'.$vector[1];
			if($this->comprobar_nombre_usuario($usuario)){
				return $usuario;
			}else{
				$usuario=$vector[1].'.'.$vector[0];
				if($this->comprobar_nombre_usuario($usuario)){
					return $usuario;
				}else{
					$usuario=$vector[0].'_'.$vector[1];	
					if($this->comprobar_nombre_usuario($usuario)){
						return $usuario;
					}else{
						$usuario=$vector[1].'_'.$vector[0];
						if($this->comprobar_nombre_usuario($usuario)){
							return $usuario;
						}else{
							$generator = new SecureRandom();
							$random = bin2hex($generator->nextBytes(4));
							return $random;
						}
					}
				}
			}
			
	 }
	 
	 private function comprobar_nombre_usuario($usuario){
	 		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT p
       			FROM ECPrincipalBundle:Usuario p
      			WHERE p.user = :user'
			)->setParameters(array('user' => $usuario));
			
			try {
    				$comprobacion = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			return 1;
			}	
			return 0;
	 }
	 
	 private function generar_password() {
			$generator = new SecureRandom();
			$random = bin2hex($generator->nextBytes(4));
			return $random;
	 }

	/**
	  * @Pdf()
	  * @Route("/comunidad/{cif}/alta/propietario/{_format}", name="ec_adminfincas_comunidad_alta_propietario")
	  * @Template("ECPrincipalBundle:Propietario:alta_propietario.html.twig")
	  */
    public function alta_propietarioAction(Request $request, $cif)
    {
    		$comunidad=$this->comprobar_comunidad($cif);
    		$bloques=$comunidad->getBloques();
    
    		$propiedad = new Propiedad();	
    		$propietario= new Propietario();
    		$propietario->setPropiedad($propiedad);
			$propiedad->setPropietario($propietario);
			$form=$this->createForm(new PropiedadType($comunidad),$propiedad);
			
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
        				$propiedad_existente=$this->comprobar_propiedad($bloque,$propiedad->getPiso());
						if(!$propiedad_existente or $propiedad_existente->getPropietario()->getRazon()!=$propietario->getRazon()){
								if(!$propiedad_existente){								
									$propiedad->setBloque($bloque);
									$bloque->addPropiedade($propiedad);	
								}else{
									//Eliminamos el antiguo propietario
									$this->eliminar_propietario($propiedad_existente->getPropietario());					
									$propiedad_existente->setPropietario($propietario);
									$propietario->setPropiedad($propiedad_existente);							
								}					
					
								$password=$this->generar_password();
								$nombre_usuario=$this->generar_nombre_usuario($propietario->getRazon());
								$propietario->setUser($nombre_usuario);
								$propietario->setPassword($password);
								$this->setSecurePassword($propietario);
								$role=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('2');
								$propietario->setRole($role[0]);
								$role[0]->addUsuario($propietario);
    			
    							$em = $this->getDoctrine()->getManager();
    							if(!$propiedad_existente){
   								$em->persist($propiedad);
   								$em->persist($bloque);
   							}else{
   								$em->persist($propiedad_existente);
   							}
   							$em->persist($propietario);
   							$em->persist($role[0]);
   							$em->flush();
   							
   							$imprimir="<tr>
   												<td>".$propietario->getPropiedad()->getBloque()->getNum()."</td>
   												<td>".$propietario->getPropiedad()->getPiso()."</td>
   												<td>".$propietario->getRazon()."</td>
													<td>".$propietario->getUser()."</td>
													<td>".$password."</td>
      										</tr>";
      										
      						$mensaje="Se han dado de alta los siguientes propietarios:";				
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
    							$bloque_csv = $this->comprobar_bloque($comunidad,$row[$headers[0]]);   							        				
    							if($bloque_csv){
    								//Comprobamos si ya existe el propietario
    								$propiedad_existente=$this->comprobar_propiedad($bloque_csv,$row[$headers[1]]);
    								if($propiedad_existente!=null and $propiedad_existente->getPropietario()->getRazon()==$row[$headers[2]]){
   				 					$flash=$this->get('translator')->trans('Error: Existen propietarios que ya han sido dados de alta.');
   				 					$this->get('session')->getFlashBag()->add('notice',$flash);
   				 					$this->get('session')->getFlashBag()->add('color','red');
   				 					return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));		
   				 				}
   				 			}else{
   				 				$flash=$this->get('translator')->trans('Error: Existen bloques que no están dados de alta');
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
    							$propietario_csv=new Propietario();									
    							if(!$propiedad_existente){
    									$propiedad_csv=new Propiedad();
										$propietario_csv->setPropiedad($propiedad_csv);
										$propiedad_csv->setPropietario($propietario_csv);
										$propiedad_csv->setBloque($bloque_csv);
										$bloque_csv->addPropiedade($propiedad_csv);
										$propiedad_csv->setPiso($row[$headers[1]]);							
								}else{
									//Eliminamos el antiguo propietario
									$this->eliminar_propietario($propiedad_existente->getPropietario());					
									$propiedad_existente->setPropietario($propietario_csv);
									$propietario_csv->setPropiedad($propiedad_existente);							
								}
		
								$propietario_csv->setRazon($row[$headers[2]]);
								$password=$this->generar_password();
								$nombre_usuario=$this->generar_nombre_usuario($propietario_csv->getRazon());
								$propietario_csv->setUser($nombre_usuario);
								$propietario_csv->setPassword($password);
								$this->setSecurePassword($propietario_csv);
								$role=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('2');
								$propietario_csv->setRole($role[0]);
								$role[0]->addUsuario($propietario_csv);
								
								$em = $this->getDoctrine()->getManager();
								if(!$propiedad_existente){
   				 				$em->persist($bloque_csv);
   				 				$em->persist($propiedad_csv);
   				 			}else{
   				 				$em->persist($propiedad_existente);
   				 			}
   				 			$em->persist($propietario_csv);
   				 			$em->persist($role[0]);
   				 			$em->flush();     
   				 			
   				 			$imprimir=$imprimir."<tr>
   												<td>".$propietario_csv->getPropiedad()->getBloque()->getNum()."</td>
   												<td>".$propietario_csv->getPropiedad()->getPiso()."</td>
   												<td>".$propietario_csv->getRazon()."</td>
													<td>".$propietario_csv->getUser()."</td>
													<td>".$password."</td>
      										</tr>";
   				 	}
   				 	
   				 	$mensaje="Se han dado de alta los siguientes propietarios:";
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
    	$comunidad=$this->comprobar_comunidad($cif); 
    	
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
		
		$password_generado=$this->generar_password();
		$propietario->setPassword($password_generado);
		$this->setSecurePassword($propietario);
   	$em->persist($propietario);
   	$em->flush();     
   	   				 			
    	$imprimir="<tr>
   						<td>".$propietario->getPropiedad()->getBloque()->getNum()."</td>
   						<td>".$propietario->getPropiedad()->getPiso()."</td>
   						<td>".$propietario->getRazon()."</td>
							<td>".$propietario->getUser()."</td>
							<td>".$password_generado."</td>
      			</tr>";
      									
      $mensaje="Nuevas contraseñas generadas:";										
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
    		$comunidad=$this->comprobar_comunidad($cif);
    		
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
        		$flash1=$this->get('translator')->trans('El propietario ');
				$flash2=$this->get('translator')->trans(' no se ha podido dar de baja. Tiene incidencias no finalizadas.');
				$this->get('session')->getFlashBag()->add('notice',$flash1.$propietario->getRazon().$flash2);
        		$this->get('session')->getFlashBag()->add('color','red');
        	}else{
        		$this->eliminar_propiedad($propietario->getPropiedad());
        		$this->eliminar_propietario($propietario);
        	
        		$flash1=$this->get('translator')->trans('El propietario ');
				$flash2=$this->get('translator')->trans(' ha sido eliminado.');
				$this->get('session')->getFlashBag()->add('notice',$flash1.$propietario->getRazon().$flash2);
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
			$comunidad=$this->comprobar_comunidad($cif);
			    		
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
    	$comunidad=$this->comprobar_comunidad($cif); 
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
    		$comunidad=$this->comprobar_comunidad($cif); 
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
    				->add('razon','text',array('label' => 'Nombre/Razón Social'))
    				->add('telefono','text', array('label' => 'Teléfono','max_length' =>9,'required'=>false))
    				->add('email','email',array('required'=>false))
    				->getForm();
    		
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    				 	$em = $this->getDoctrine()->getManager();
               	
   					$em->persist($propietario);
   					$em->flush();
    			
						$this->get('session')->getFlashBag()->add('notice','Los datos personales han sido actualizados.');
   				 	$this->get('session')->getFlashBag()->add('color','green');
   				 	return $this->redirect($this->generateUrl('ec_propietario_perfil'));				
        	}
        	
        	return $this->render('ECPrincipalBundle:Propietario:modificacion_datospersonales.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    	
    }
}