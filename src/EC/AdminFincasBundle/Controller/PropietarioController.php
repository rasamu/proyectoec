<?php

namespace EC\AdminFincasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\ComunidadBundle\Entity\Comunidad;
use EC\ComunidadBundle\Entity\Bloque;
use EC\PrincipalBundle\Entity\Usuario;
use EC\PrincipalBundle\Entity\Role;
use EC\PropietarioBundle\Entity\Propiedad;
use EC\PrincipalBundle\Entity\Csv;
use EC\PropietarioBundle\Form\Type\PropiedadType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Ps\PdfBundle\Annotation\Pdf;

class PropietarioController extends Controller
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
    		
    		try{
    			$bloque=$query->getSingleResult();	
			} catch (\Doctrine\Orm\NoResultException $e) {
				return null;
			}
			return $bloque;
	 }
	 
	 private function setSecurePassword($entity) {
			$entity->setSalt(md5(time()));
			$encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
			$password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
			$entity->setPassword($password);
	 }
	 
	 private function generar_usuario($nombre) {
	 		$nombre=strtolower($nombre);//Convierte a minusculas
	 		$nombre = preg_replace('/[.*-_;]/', '', $nombre);//Eliminamos caracteres especiales
	 		$vector=preg_split("/[\s,]+/",$nombre);//Divide en elementos de un vector
	 		
			$usuario=$vector[0].'.'.$vector[1];
			if($this->comprobar_usuario($usuario)){
				return $usuario;
			}else{
				$usuario=$vector[1].'.'.$vector[0];
				if($this->comprobar_usuario($usuario)){
					return $usuario;
				}else{
					$usuario=$vector[0].'_'.$vector[1];	
					if($this->comprobar_usuario($usuario)){
						return $usuario;
					}else{
						$usuario=$vector[1].'_'.$vector[0];
						if($this->comprobar_usuario($usuario)){
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
	 
	 private function comprobar_usuario($usuario){
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
	  * @Route("/comunidad/{cif}/alta/propietario", name="ec_adminfincas_comunidad_alta_propietario")
	  * @Template("ECAdminFincasBundle:Default:alta_propietario.html.twig")
	  */
    public function alta_propietarioAction(Request $request, $cif)
    {
    		$comunidad=$this->comprobar_comunidad($cif);
    		$bloques=$comunidad->getBloques();
    
    		$propiedad = new Propiedad();	
    		$propietario= new Usuario();
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
    					$bloque=$form->get('bloque')->getData();
					
						$propiedad->setBloque($bloque);
						$bloque->addPropiedade($propiedad);						
					
						$password=$this->generar_password();
						$usuario=$this->generar_usuario($propietario->getNombre());
						$propietario->setUser($usuario);
						$propietario->setPassword($password);
						$this->setSecurePassword($propietario);
						$role=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('2');
						$propietario->setRole($role[0]);
						$role[0]->addUsuario($propietario);
    			
    					$em = $this->getDoctrine()->getManager();
   					$em->persist($propiedad);
   					$em->persist($propietario);
   					$em->persist($role[0]);
   					$em->flush();
    			
    					$flash=$this->get('translator')->trans('Propietario registrado con éxito.');
						$this->get('session')->getFlashBag()->add('notice',$flash.' Contraseña:'.$password);
   					$this->get('session')->getFlashBag()->add('color','green');
   					return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));
        			}
        			//Form CSV
        			if ($form_csv->isValid()) {
        				$reader = new \EasyCSV\Reader($csv->getFile());
        				$reader->setDelimiter(';');
        				$headers=$reader->getHeaders();
        				$aux=0;
        				if($headers[0]=='nfinca' && $headers[1]=='piso' && $headers[2]=='razon') {
        					while($row = $reader->getRow()){  			
    							$bloque_csv = $this->comprobar_bloque($comunidad,$row[$headers[0]]);
    							
    							if($bloque_csv){
									$propiedad_csv=new Propiedad();
									$propietario_csv=new Usuario();
									$propietario_csv->setPropiedad($propiedad_csv);
									$propiedad_csv->setPropietario($propietario_csv);
									$propiedad_csv->setBloque($bloque_csv);
									$bloque_csv->addPropiedade($propiedad_csv);
									
									$propiedad_csv->setPiso($row[$headers[1]]);						
									$propietario_csv->setNombre($row[$headers[2]]);
									$password=$this->generar_password();
									$usuario=$this->generar_usuario($propietario_csv->getNombre());
									$propietario_csv->setUser($usuario);
									$propietario_csv->setPassword($password);
									$this->setSecurePassword($propietario_csv);
									$role=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('2');
									$propietario_csv->setRole($role[0]);
									$role[0]->addUsuario($propietario_csv);
								
									$em = $this->getDoctrine()->getManager();
   				 				$em->persist($bloque_csv);
   				 				$em->persist($propiedad_csv);
   				 				$em->persist($propietario_csv);
   				 				$em->persist($role[0]);
   				 				$em->flush();
   				 				
   				 			}else{
   				 				$aux=1;	
   				 			}
    						}
    						if($aux==0){
    							$flash=$this->get('translator')->trans('Registro de propietarios realizado con éxito.');
    							$this->get('session')->getFlashBag()->add('notice',$flash);
   				 			$this->get('session')->getFlashBag()->add('color','green');
   				 			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));
   				 		}else{
   				 			$flash=$this->get('translator')->trans('Algunos números de bloques del fichero no existen, por lo que no se han podido dar de alta.');
   				 			$this->get('session')->getFlashBag()->add('notice',$flash);
   				 			$this->get('session')->getFlashBag()->add('color','red');
   				 			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));	
   				 		}
            		}else{
            			$flash=$this->get('translator')->trans('Cabeceras del fichero CSV no válidas.');
            			$this->get('session')->getFlashBag()->add('notice',$flash);
   				 		$this->get('session')->getFlashBag()->add('color','red');
   				 		return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));
            		}				
        			}
        		}
        		return $this->render('ECAdminFincasBundle:Default:alta_propietario.html.twig',
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
	  * @Route("/comunidad/{cif}/propietario/eliminar/{id}", name="ec_adminfincas_comunidad_eliminar_propietario")
	  */
    public function comunidad_eliminar_propietarioAction($cif, $id)
    {
    		$comunidad=$this->comprobar_comunidad($cif);
    		
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT p
       			FROM ECPrincipalBundle:Usuario p
      			WHERE p.id = :id'
			)->setParameters(array('id' => $id));
			
			try {
    				$propietario = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}
			$propiedad=$propietario->getPropiedad();
			
			$flash1=$this->get('translator')->trans('El propietario ');
			$flash2=$this->get('translator')->trans(' ha sido eliminado.');
			$this->get('session')->getFlashBag()->add('notice',$flash1.$propietario->getNombre().$flash2);
        	$this->get('session')->getFlashBag()->add('color','green');
    		$em = $this->getDoctrine()->getEntityManager();
    		$em->remove($propietario);
    		$em->remove($propiedad);
    		$em->flush();
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
			
        	return $this->render('ECAdminFincasBundle:Default:comunidad_listado_propietarios.html.twig',array(
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
    	
    	$filename = "propietarios_".$comunidad->getCif().".pdf";    	        
    	$response=$this->render(sprintf('ECAdminFincasBundle:Default:comunidad_listado_propietarios_pdf.%s.twig', $format), array(
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
    	
			$filename = "propietarios_".$comunidad->getCif().".csv";
	
			$response = $this->render('ECAdminFincasBundle:Default:comunidad_listado_propietarios_csv.html.twig', array('bloques' => $bloques));
			$response->headers->set('Content-Type', 'text/csv');

			$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	}
    
    /**
	  * @Route("/comunidad/{cif}/nombrarpresidente/{dni}", name="ec_adminfincas_nombrar_presidente")
	  * @Template()
	  */
    public function nombrar_presidenteAction($cif, $id){
    		$comunidad=$this->comprobar_comunidad($cif);
    		$role_presidente=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('3');
						
    		/*Buscamos y eliminamos Presidente anterior*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT u
					FROM ECPrincipalBundle:Usuario u
					WHERE u.role = :role_presidente and u.propiedad IN
					(SELECT p FROM ECPropietarioBundle:Propiedad p WHERE p.bloque IN
					(SELECT b FROM ECComunidadBundle:Bloque b WHERE b.comunidad = :comunidad))'
			)->setParameters(array('comunidad'=>$comunidad,'role_presidente'=>$role_presidente[0],));
			
			try {
				$propietario = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$propietario = null;
			}
			$aux=0;
			if($propietario){
				$aux=1;
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
       			FROM ECPrincipalBundle:Usuario u
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
			
			$flash=$this->get('translator')->trans(' ha sido nombrado nuevo Presidente.');
			$this->get('session')->getFlashBag()->add('notice',$presidente->getNombre().$flash.' Aux:'.$aux);
        	$this->get('session')->getFlashBag()->add('color','green');
			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_propietarios', array('cif' => $cif)));
    }
    
    /**
	  * @Route("/comunidad/{cif}/nombrarvicepresidente/{dni}", name="ec_adminfincas_nombrar_vicepresidente")
	  * @Template()
	  */
    public function nombrar_vicepresidenteAction($cif, $id){
    	$comunidad=$this->comprobar_comunidad($cif);
    	$bloques=$comunidad->getBloques();
    	$role_vicepresidente=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('4');
    				
    	/*Buscamos y eliminamos Vicepresidente anterior*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT u
					FROM ECPrincipalBundle:Usuario u
					WHERE u.role = :role and u.propiedad IN
					(SELECT p.id FROM ECPropietarioBundle:Propiedad p WHERE p.bloque IN
					(SELECT b.id FROM ECComunidadBundle:Bloque b WHERE b.comunidad = :comunidad))'
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
       			FROM ECPrincipalBundle:Usuario u
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
			$flash=$this->get('translator')->trans(' ha sido nombrado nuevo Vicepresidente.');
			$this->get('session')->getFlashBag()->add('notice',$vicepresidente->getNombre().$flash);
        	$this->get('session')->getFlashBag()->add('color','green');
			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_propietarios', array('cif' => $cif)));
    }
}