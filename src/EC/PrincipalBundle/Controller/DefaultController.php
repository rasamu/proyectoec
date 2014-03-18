<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\AdminFincasBundle\Entity\AdminFincas;
use EC\PropietarioBundle\Entity\Propietario;
use EC\ComunidadBundle\Entity\Comunidad;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use EC\PrincipalBundle\Entity\City;
use EC\PrincipalBundle\Entity\Province;

class DefaultController extends Controller
{
	/**
	  * @Route("/", name="ec_principal_homepage")
	  * @Template("ECPrincipalBundle:Default:index.html.twig")
	  */
    public function indexAction()
    {
        	return $this->render('ECPrincipalBundle:Default:index.html.twig');
    }
    
    /**
	  * @Route("/check", name="ec_principal_check")
	  */
    public function checkAction()
    {
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
				return $this->redirect($this->generateUrl('ec_adminfincas_homepage'), 301);
			}else{
				if($this->get('security.context')->isGranted('ROLE_VECINO')){
						return $this->redirect($this->generateUrl('ec_propietario_homepage'), 301);
				}else{
					return $this->redirect($this->generateUrl('ec_principal_homepage'), 301);
				}	
			}
    }
    
    private function setSecurePassword($entity) {
			$entity->setSalt(md5(time()));
			$encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
			$password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
			$entity->setPassword($password);
	 }
    
    /**
	  * @Route("/alta", name="ec_principal_alta_adminfincas")
	  * @Template("ECPrincipalBundle:Default:alta_adminfincas.html.twig")
	  */
    public function alta_adminfincasAction(Request $request)
    {
    		$adminfincas = new AdminFincas();
    		
    		$form = $this ->createFormBuilder($adminfincas)
    				->add('dni','text', array('label' => 'DNI','max_length' =>9))
    				->add('n_colegiado','text', array('label' => 'NºColegiado','max_length' =>9,'required' => false))
    				->add('nombre','text')
    				->add('apellidos','text')
    				->add('telefono','integer', array('label' => 'Teléfono','max_length' =>9))
    				->add('fax','integer',array('required' => false,'max_length' =>9))
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
    				$num=$form->get('dni')->getData();
					$comprobacion=$this->getDoctrine()
        				->getRepository('ECAdminFincasBundle:AdminFincas')
        				->find($num);
            	
            	if($comprobacion){
            		$this->get('session')->getFlashBag()->add('notice','Administrador de Fincas ya registrado.');
   				 	$this->get('session')->getFlashBag()->add('color','red');   				 	
						return $this->render('ECPrincipalBundle:Default:mensaje.html.twig');
            	}else{    				     				 
    				 	$this->setSecurePassword($adminfincas);
    			
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($adminfincas);
   				 	$em->flush();
   				 	
   				 	$message = \Swift_Message::newInstance()
        				->setSubject('Alta EntreComunidades')
        				->setFrom('rasamu24@gmail.com')
        				->setTo($form->get('email')->getData())
        				->setContentType('text/html')
        				->setBody($this->renderView('ECPrincipalBundle:Default:email_alta.txt.twig', array('nombre'=>$form->get('nombre')->getData().' '.$form->get('apellidos')->getData(),'usuario'=>$form->get('dni')->getData(),'pass'=>$form->get('password')->getData())));
    					$this->get('mailer')->send($message);
    			
						$this->get('session')->getFlashBag()->add('notice','Alta realizada con éxito.');
   				 	$this->get('session')->getFlashBag()->add('color','green');   				 	
						return $this->render('ECPrincipalBundle:Default:mensaje.html.twig');
        			}
        	}
        	
        	return $this->render('ECPrincipalBundle:Default:alta_adminfincas.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    /**
	  * @Route("/contacto", name="ec_principal_contacto")
	  * @Template("ECPrincipalBundle:Default:contacto.html.twig")
	  */
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
        				->setSubject('Contacto EntreComunidades')
        				->setFrom($form->get('Email')->getData())
        				->setTo('rasamu24@hotmail.com')
        				->setContentType('text/html')
        				->setBody($this->renderView('ECPrincipalBundle:Default:email_contacto.txt.twig', array('nombre'=>$form->get('Nombre')->getData(),'email'=>$form->get('Email')->getData(),'mensaje'=>$form->get('Mensaje')->getData())));
    			$this->get('mailer')->send($message);
        
        		$this->get('session')->getFlashBag()->add('notice','Formulario de contacto enviado.');
   			$this->get('session')->getFlashBag()->add('color','green');   				 	
				return $this->render('ECPrincipalBundle:Default:mensaje.html.twig');
   		}
    	
      	return $this->render('ECPrincipalBundle:Default:contacto.html.twig', array(
            	'form' => $form->createView(),
       		));
    }
    
    /**
	  * @Route("/login", name="login")
	  * @Template("ECPrincipalBundle:Default:login.html.twig")
	  */
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
    
   /**
	* @Route("/provinces", name="select_provinces")
	* @Template()
	*/
    public function provincesAction()
    {
        $country_id = $this->getRequest()->request->get('country_id');

        $em = $this->getDoctrine()->getManager();

        $provinces = $em->getRepository('ECPrincipalBundle:Province')->findByCountryId($country_id);

        return array(
            'provinces' => $provinces
        );
    }

	/**
	* @Route("/cities", name="select_cities")
	* @Template()
	*/
    public function citiesAction()
    {
        $province_id = $this->getRequest()->request->get('province_id');

        $em = $this->getDoctrine()->getManager();

        $cities = $em->getRepository('ECPrincipalBundle:City')->findByProvinceId($province_id);

        return array(
            'cities' => $cities
        );
    }
}
