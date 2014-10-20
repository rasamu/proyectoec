<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\Role;
use EC\PrincipalBundle\Entity\Usuario;
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
						return $this->redirect($this->generateUrl('ec_tablon_comunidad'), 301);
				}else{
					return $this->redirect($this->generateUrl('ec_principal_homepage'), 301);
				}	
			}
    }
    
    /**
	  * @Route("/adfincas", name="ec_principal_eres_adminfincas")
	  * @Template("ECPrincipalBundle:Default:eres_adminfincas.html.twig")
	  */
    public function eres_adminfincasAction()
    {   		
        	return $this->render('ECPrincipalBundle:Default:eres_adminfincas.html.twig');
    }
    
    /**
	  * @Route("/contacto", name="ec_principal_contacto")
	  * @Template("ECPrincipalBundle:Default:contacto.html.twig")
	  */
    public function contactoAction(Request $request)
    {
			$defaultData = array('message' => 'Escribe aqui.');
    		$form = $this->createFormBuilder($defaultData)
    			->setAction($this->generateUrl('ec_principal_contacto'))
        		->add('Nombre', 'text',array('label'=>'Nombre'))
        		->add('Email', 'email')
        		->add('Mensaje', 'textarea',array('max_length'=>500, 'label'=>'Mensaje', 'attr'=>array('rows' => 12 , 'cols' => 29)))
        		->getForm();	
        
     		$form->handleRequest($request);
     
     		if ($form->isValid()) {
       		 // data es un array con claves 'name', 'email', y 'message'
       		 $data = $form->getData();
       	 
        		$message = \Swift_Message::newInstance()
        				->setSubject('Contacto EntreComunidades')
        				->setFrom($form->get('Email')->getData())
        				->setTo('info@proyectoec.hol.es')
        				->setContentType('text/html')
        				->setBody($this->renderView('ECPrincipalBundle:Default:email_contacto.txt.twig', array('nombre'=>$form->get('Nombre')->getData(),'email'=>$form->get('Email')->getData(),'mensaje'=>$form->get('Mensaje')->getData())));
    			$this->get('mailer')->send($message);
        
        		$flash=$this->get('translator')->trans('Formulario de contacto enviado.');
        		$this->get('session')->getFlashBag()->add('notice', $flash);
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
