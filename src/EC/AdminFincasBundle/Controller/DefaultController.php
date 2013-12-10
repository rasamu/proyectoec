<?php

namespace EC\AdminFincasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use EC\ComunidadBundle\Entity\Comunidad;
use EC\VecinoBundle\Entity\Vecino;
use EC\AdminFincasBundle\Entity\AdminFincas;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class DefaultController extends Controller
{
    public function alta_comunidadAction(Request $request)
    {
    		$comunidad = new Comunidad();
    		
    		$form = $this ->createFormBuilder($comunidad)
    				->add('cif','text',array('max_length' =>9))
    				->add('direccion','text',array('label' => 'Dirección'))
    				->add('provincia','text')
    				->add('localidad','text')
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
    				 $cif=$form->get('cif')->getData();
					 $comprobacion=$this->getDoctrine()
        				->getRepository('ECComunidadBundle:Comunidad')
        				->find($cif);
        				
        			 if($comprobacion){
        			 	return $this->render('ECAdminFincasBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Comunidad ya registrada.',
        	      		));
        			 }else{
    				 	$comunidad->setAdministrador($this->getUser());
    			
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($comunidad);
   				 	$em->flush();
    					
						return $this->render('ECAdminFincasBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Alta de comunidad realizada con éxito.',
        	      		));
        			}
        	}
        	
        	return $this->render('ECAdminFincasBundle:Default:alta_comunidad.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    public function listado_comunidadesAction()
    {
			$em = $this->getDoctrine()->getManager();
			$comunidades = $em->getRepository('ECComunidadBundle:Comunidad')
               ->findAllByNColegiado($this->getUser());

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
}
