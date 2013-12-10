<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use EC\AdminFincasBundle\Entity\AdminFincas;
use EC\VecinoBundle\Entity\Vecino;
use EC\ComunidadBundle\Entity\Comunidad;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class DefaultController extends Controller
{
    public function indexAction()
    {
        	return $this->render('ECPrincipalBundle:Default:index.html.twig');
    }
    
    private function setSecurePassword($entity) {
			$entity->setSalt(md5(time()));
			$encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
			$password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
			$entity->setPassword($password);
	}
    
    public function alta_adminfincasAction(Request $request)
    {
    		$adminfincas = new AdminFincas();
    		
    		$form = $this ->createFormBuilder($adminfincas)
    				->add('n_colegiado','text', array('label' => 'NºColegiado','max_length' =>9))
    				->add('nombre','text')
    				->add('apellidos','text')
    				->add('telefono','integer', array('label' => 'Teléfono'))
    				->add('fax','integer')
    				->add('email','text')
    				->add('direccion','text', array('label' => 'Dirección'))
    				->add('provincia','text')
    				->add('localidad','text')
    				->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Las dos contraseñas deben coincidir',
                'required' => true,
                'first_options'  => array('label' => 'Contraseña','max_length' =>9),
    				 'second_options' => array('label' => 'Confirmación','max_length' =>9),
    				))
    				->getForm();
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    				$num=$form->get('n_colegiado')->getData();
					$comprobacion=$this->getDoctrine()
        				->getRepository('ECAdminFincasBundle:AdminFincas')
        				->find($num);
            	
            	if($comprobacion){
						return $this->render('ECPrincipalBundle:Default:error.html.twig',
        	       		array('mensaje' => 'Administrador de Fincas ya registrado.',
        	      		));
            	}else{    				     				 
    				 	$this->setSecurePassword($adminfincas);
    			
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($adminfincas);
   				 	$em->flush();
    			
						return $this->render('ECPrincipalBundle:Default:mensaje.html.twig',
        	       		array('mensaje1'=>'Alta Administrador de Fincas','mensaje2' => 'Alta realizada con éxito.',
        	      		));
        			}
        	}
        	
        	return $this->render('ECPrincipalBundle:Default:alta_adminfincas.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    public function alta_vecinoAction(Request $request)
    {
    		$vecino = new Vecino();
    		
    		$form = $this ->createFormBuilder($vecino)
    				->add('dni','text',array('max_length'=>9))
    				->add('nombre','text')
    				->add('apellidos','text')
    				->add('telefono','integer', array('label' => 'Teléfono'))
    				->add('email','text')
    				->add('portal','text', array('label' => 'Portal'))
    				->add('piso','text', array('label' => 'Piso'))
    				->add('provincia','text')
    				->add('localidad','text')
    				->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Las dos contraseñas deben coincidir',
                'required' => true,
                'first_options'  => array('label' => 'Contraseña','max_length' =>9),
    				 'second_options' => array('label' => 'Confirmación','max_length' =>9),
    				))
    				->add('comunidad','entity',array('class'=>'ECComunidadBundle:Comunidad',
    				'property'=>'cif'))
    				->getForm();
    		
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    				$dni=$form->get('dni')->getData();
					$comprobacion=$this->getDoctrine()
        				->getRepository('ECVecinoBundle:Vecino')
        				->find($dni);
            	
            	if($comprobacion){
						return $this->render('ECPrincipalBundle:Default:error.html.twig',
        	       		array('mensaje' => 'Vecino ya registrado.',
        	      		));
            	}else{
    				 	 $this->setSecurePassword($vecino);
    			
    				 	 $em = $this->getDoctrine()->getManager();
               	
   					 $em->persist($vecino);
   					 $em->flush();
    			
							return $this->render('ECPrincipalBundle:Default:mensaje.html.twig',
        	       		array('mensaje1'=>'Alta Vecino.','mensaje2' => 'Alta realizada con éxito.',
        	      		));
					}   				
        	}
        	
        	return $this->render('ECPrincipalBundle:Default:alta_vecino.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    public function contactoAction(Request $request)
    {
			$defaultData = array('message' => 'Escribe aqui.');
    		$form = $this->createFormBuilder($defaultData)
        		->add('Nombre', 'text')
        		->add('Email', 'email')
        		->add('Mensaje', 'textarea',array('max_length'=>500, 'attr'=>array('rows' => 12 , 'cols' => 29)))
        		->getForm();	
        
     		$form->handleRequest($request);
     
     		if ($form->isValid()) {
       		 // data es un array con claves 'name', 'email', y 'message'
       		 $data = $form->getData();
       	 
        		$message = \Swift_Message::newInstance()
        		->setSubject('EntreComunidades')
        		->setFrom('rasamu24@gmail.com')
        		->setTo('rasamu24@hotmail.com')
        		->setBody(
           		 $this->renderView(
               		 'ECPrincipalBundle:Default:email.txt.twig', array('data' => $data)));
   	  
   	  		$this->get('mailer')->send($message);
        
				return $this->render('ECPrincipalBundle:Default:mensaje.html.twig',
        	       		array('mensaje1'=>'Contacto.','mensaje2' => 'Formulario de contacto enviado.',
        	      		));
   		}
    	
      	return $this->render('ECPrincipalBundle:Default:contacto.html.twig', array(
            	'form' => $form->createView(),
       		));
    }
    
    public function loginAction()
    {
		  $request = $this->getRequest();
        $session = $request->getSession();

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        }	
    	
    		return $this->render('ECPrincipalBundle:Default:login.html.twig', array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));
    }
}
