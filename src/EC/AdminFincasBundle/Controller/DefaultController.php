<?php

namespace EC\AdminFincasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\ComunidadBundle\Form\Type\ComunidadType;
use EC\ComunidadBundle\Entity\Comunidad;
use EC\VecinoBundle\Entity\Vecino;
use EC\AdminFincasBundle\Entity\AdminFincas;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use EC\PrincipalBundle\Entity\City;
use EC\PrincipalBundle\Entity\Province;

class DefaultController extends Controller
{    
    /**
	  * @Route("/alta/comunidad", name="ec_adminfincas_alta_comunidad")
	  * @Template("ECBundle:Default:alta_comunidad.html.twig")
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
        			 	
        			 		return $this->render('ECAdminFincasBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Alta de comunidad realizada con éxito.',
        	      		));
        			 }else{
        			 	if($comprobacion->getAdministrador()){
        			 		/*Ya existe*/
        			 		return $this->render('ECAdminFincasBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Comunidad ya registrada.',
        	      		));
   				 	}else{
							/*Actualizacion*/
							$cuidad=$form->get('city')->getData();
							
							$comprobacion->setDireccion($form->get('direccion')->getData());
							$comprobacion->setNPlazasGaraje($form->get('n_plazas_garaje')->getData());
							$comprobacion->setNLocalesComerciales($form->get('n_locales_comerciales')->getData());
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
   				 		
   				 		return $this->render('ECAdminFincasBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Alta de comunidad realizada con éxito.',
        	      		));
   				 	}
        			}
        	}      	
        	return $this->render('ECAdminFincasBundle:Default:alta_comunidad.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    public function listado_comunidadesAction()
    {
         $comunidades=$this->getUser()->getComunidades();      

        	return $this->render('ECAdminFincasBundle:Default:listado_comunidades.html.twig',array(
        		'comunidades' => $comunidades
        	));
    }
    
    public function comunidad_listado_vecinosAction($cif)
    {
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT c
       			FROM ECComunidadBundle:Comunidad c
      			WHERE c.cif = :cif'
			)->setParameter('cif', $cif);
			$comunidad = $query->getSingleResult();  
         $vecinos=$comunidad->getVecinos();
			
        	return $this->render('ECAdminFincasBundle:Default:comunidad_listado_vecinos.html.twig',array(
        		'vecinos' => $vecinos, 'comunidad' =>$comunidad
        	));
    }
    
    public function modificacion_perfilAction(Request $request)
    {
			$adminfincas = $this->getUser();
    		
    		$form = $this ->createFormBuilder($adminfincas)
    				->add('nombre','text')
    				->add('apellidos','text')
    				->add('telefono','integer', array('label' => 'Teléfono'))
    				->add('fax','integer')
    				->add('email','text')
    				->add('direccion','text', array('label' => 'Dirección'))
    				->add('provincia','text')
    				->add('localidad','text')
    				->getForm();
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {				     				     			
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($adminfincas);
   				 	$em->flush();
    			
						return $this->render('ECAdminFincasBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Modificación realizada con éxito.',
        	      		));
        	}
        	
        	return $this->render('ECAdminFincasBundle:Default:modificacion_datospersonales.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    public function modificacion_contraseñaAction(Request $request)
    {
    		$form = $this->createFormBuilder()
        		->add('pass', 'password', array('label' => 'Contraseña','max_length' =>9))
				->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Las dos contraseñas deben coincidir',
                'required' => true,
                'first_options'  => array('label' => 'Nueva Contraseña','max_length' =>9),
    				 'second_options' => array('label' => 'Confirmación','max_length' =>9),
    				))
       		->getForm();
 
    		$form->handleRequest($request);
 
    		if ($form->isValid()) {
       		 	$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT v
       				FROM ECAdminFincasBundle:AdminFincas v
      				WHERE v.n_colegiado = :n_colegiado'
					)->setParameter('n_colegiado', $this->getUser());
					$adminfincas = $query->getSingleResult();
					
					$encoder_old = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
					$clave_old = $encoder_old->encodePassword($form->get('pass')->getData(),$adminfincas->getSalt());
					
            	if($adminfincas->getPassword()==$clave_old){
						$adminfincas->setSalt(md5(time()));
            		$encoder_new = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
						$clave_new = $encoder_new->encodePassword($form->get('password')->getData(),$adminfincas->getSalt());
						$adminfincas->setPassword($clave_new);
						
   				 	$em->persist($adminfincas);
    					$em->flush();
    					
						return $this->render('ECAdminFincasBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Cambio de contraseña realizado con éxito.',
        	      		));
            	}else{    				     				 
    				 	return $this->render('ECAdminFincasBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Contraseña no válida.',
        	      		));
        			}
    		}
    	
    		return $this->render('ECAdminFincasBundle:Default:modificacion_contraseña.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
 	}
 	
 	public function editar_comunidadAction($cif, Request $request)
    {
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT c
       			FROM ECComunidadBundle:Comunidad c
      			WHERE c.cif = :cif'
			)->setParameter('cif', $cif);
			$comunidad = $query->getSingleResult();  
	
			$form = $this ->createFormBuilder($comunidad)
    				->add('n_plazas_garaje','integer',array('label' => 'Nº Plazas de garaje'))
    				->add('n_locales_comerciales','integer',array('label' => 'Nº Locales Comerciales'))
    				->add('n_piscinas','integer',array('label' => 'Nº Piscinas'))
    				->add('n_pistas','integer',array('label' => 'Nº Pistas'))
    				->add('gimnasio','choice',array('choices'=>array('1' => 'Si', '0' => 'No')))
    				->add('ascensor','choice',array('choices'=>array('1' => 'Si', '0' => 'No')))
    				->add('conserjeria','choice',array('choices'=>array('1' => 'Si', '0' => 'No')))
    				->getForm();
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {	
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($comunidad);
   				 	$em->flush();
    					
						return $this->render('ECAdminFincasBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Modificación de comunidad realizada con éxito.',
        	      		));
        	}
        	
        	return $this->render('ECAdminFincasBundle:Default:editar_comunidad.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    public function eliminar_comunidadAction($cif)
    {
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT c
       			FROM ECComunidadBundle:Comunidad c
      			WHERE c.cif = :cif'
			)->setParameter('cif', $cif);
			$comunidad = $query->getSingleResult(); 
        	
        	$this->getUser()->removeComunidade($comunidad);
        	$comunidad->setAdministrador();
			$em = $this->getDoctrine()->getManager();
   	   $em->persist($comunidad);
   	   $em->flush();        	
        	
        	return $this->redirect($this->generateUrl('ec_adminfincas_listado_comunidades'), 301);
    }
    
    /**
	  * @Route("/comunidad/{cif}/nombrarpresidente/{dni}", name="ec_adminfincas_nombrar_presidente")
	  */
    public function nombrar_presidenteAction($cif, $dni){
    		/*Buscamos y eliminamos Presidente anterior*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT v
       			FROM ECVecinoBundle:Vecino v
      			WHERE v.comunidad = :cif and v.tipo = :tipo'
			)->setParameters(array('cif' => $cif,'tipo'=>'Presidente',));
			
			try {
				$vecino = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$vecino = null;
			}
			
			if($vecino){
				$vecino->setTipo('Vecino');
				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($vecino);
   	   	$em->flush();  
			}
			
			/*Buscamos y nombramos al nuevo presidente*/
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT v
       			FROM ECVecinoBundle:Vecino v
      			WHERE v.comunidad = :cif and v.dni = :dni'
			)->setParameters(array('cif' => $cif, 'dni' => $dni,));
			
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
			
			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_vecinos', array('cif' => $cif)), 301);
    }
    
    /**
	  * @Route("/comunidad/{cif}/nombrarpresidente/{dni}", name="ec_adminfincas_nombrar_presidente")
	  */
    public function nombrar_vicepresidenteAction($cif, $dni){
    	/*Buscamos y eliminamos Vicepresidente anterior*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT v
       			FROM ECVecinoBundle:Vecino v
      			WHERE v.comunidad = :cif and v.tipo = :tipo'
			)->setParameters(array('cif' => $cif,'tipo'=>'Vicepresidente',));
			
			try {
				$vecino = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
    			$vecino = null;
			}
			
			if($vecino){
				$vecino->setTipo('Vecino');
				$em = $this->getDoctrine()->getManager();
   	 		$em->persist($vecino);
   	   	$em->flush();  
			}
			
			/*Buscamos y nombramos al nuevo Vicepresidente*/
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT v
       			FROM ECVecinoBundle:Vecino v
      			WHERE v.comunidad = :cif and v.dni = :dni'
			)->setParameters(array('cif' => $cif, 'dni' => $dni,));
			
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
			
			return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_listado_vecinos', array('cif' => $cif)), 301);
    }
}
