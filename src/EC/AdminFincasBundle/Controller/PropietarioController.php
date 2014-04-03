<?php

namespace EC\AdminFincasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\ComunidadBundle\Entity\Comunidad;
use EC\ComunidadBundle\Entity\Bloque;
use EC\PropietarioBundle\Entity\Propietario;
use EC\PropietarioBundle\Entity\Propiedad;
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
					$usuario=$vector[0].'.'.$vector[1].$vector[2];	
					if($this->comprobar_usuario($usuario)){
						return $usuario;
					}else{
						$usuario=$vector[0].'_'.$vector[1].$vector[2];
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
       			FROM ECPropietarioBundle:Propietario p
      			WHERE p.usuario = :usuario'
			)->setParameters(array('usuario' => $usuario));
			
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
    		$propietario= new Propietario();
    		$propietario->setPropiedad($propiedad);
			$propiedad->setPropietario($propietario);
			
			$form=$this->createForm(new PropiedadType($comunidad),$propiedad);
    		
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    				$bloque=$form->get('bloque')->getData();
					
					$propiedad->setBloque($bloque);
					$bloque->addPropiedade($propiedad);						
					
					$password=$this->generar_password();
					$usuario=$this->generar_usuario($propietario->getNombre());
					$propietario->setUsuario($usuario);
					$propietario->setPassword($password);
					$this->setSecurePassword($propietario);
    			
    				$em = $this->getDoctrine()->getManager();
   				$em->persist($propiedad);
   				$em->persist($propietario);
   				$em->flush();
    			
					$this->get('session')->getFlashBag()->add('notice','Propietario registrado con éxito. Contraseña: '.$password);
   				$this->get('session')->getFlashBag()->add('color','green');
   				return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_alta_propietario', array('cif'=>$comunidad->getCif())));
        	}
        	
        	return $this->render('ECAdminFincasBundle:Default:alta_propietario.html.twig',
        	       		array('form' => $form->createView(), 'comunidad'=>$comunidad,
        	      		));
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
       			FROM ECPropietarioBundle:Propietario p
      			WHERE p.id = :id'
			)->setParameters(array('id' => $id));
			
			try {
    				$propietario = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}
			$propiedad=$propietario->getPropiedad();
			
			$this->get('session')->getFlashBag()->add('notice','El propietario '.$propietario->getNombre().' ha sido eliminado.');
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
    					
    		/*Buscamos y eliminamos Presidente anterior*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT v
       			FROM ECPropietarioBundle:Propietario v
      			WHERE v.comunidad = :cif and v.tipo = :tipo'
			)->setParameters(array('cif' => $cif,'tipo'=>'Presidente',));
			
			try {
				$propietario = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$propietario = null;
			}
			
			if($propietario){
				$propietario->setTipo('Propietario');
				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($propietario);
   	   	$em->flush();  
			}
			
			/*Buscamos y nombramos al nuevo presidente*/
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT v
       			FROM ECPropietarioBundle:Propietario v
      			WHERE v.comunidad = :cif and v.id = :id'
			)->setParameters(array('cif' => $cif, 'id' => $id,));
			
			try {
				$presidente = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$presidente = null;
			}
			
			if($presidente){
				$presidente->setTipo('Presidente');
				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($presidente);
   	   	$em->flush();  
			}
			$this->get('session')->getFlashBag()->add('notice',$presidente->getNombre().' ha sido nombrado nuevo Presidente.');
        	$this->get('session')->getFlashBag()->add('color','green');
			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_propietarios', array('cif' => $cif)));
    }
    
    /**
	  * @Route("/comunidad/{cif}/nombrarvicepresidente/{dni}", name="ec_adminfincas_nombrar_vicepresidente")
	  * @Template()
	  */
    public function nombrar_vicepresidenteAction($cif, $id){
    	$comunidad=$this->comprobar_comunidad($cif);
    				
    	/*Buscamos y eliminamos Vicepresidente anterior*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT v
       			FROM ECPropietarioBundle:Propietario v
      			WHERE v.comunidad = :cif and v.tipo = :tipo'
			)->setParameters(array('cif' => $cif,'tipo'=>'Vicepresidente',));
			
			try {
				$propietario = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$propietario = null;
			}
			
			if($propietario){
				$propietario->setTipo('Propietario');
				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($propietario);
   	   	$em->flush();  
			}
			
			/*Buscamos y nombramos al nuevo Vicepresidente*/
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT v
       			FROM ECPropietarioBundle:Propietario v
      			WHERE v.comunidad = :cif and v.id = :id'
			)->setParameters(array('cif' => $cif, 'id' => $id,));
			
			try {
				$vicepresidente = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$vicepresidente = null;
			}
			
			if($vicepresidente){
				$vicepresidente->setTipo('Vicepresidente');
				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($vicepresidente);
   	   	$em->flush();  
			}
			
			$this->get('session')->getFlashBag()->add('notice',$vicepresidente->getNombre().' ha sido nombrado nuevo Vicepresidente.');
        	$this->get('session')->getFlashBag()->add('color','green');
			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_propietarios', array('cif' => $cif)));
    }
}