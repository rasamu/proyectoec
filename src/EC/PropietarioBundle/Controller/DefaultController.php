<?php

namespace EC\PropietarioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use EC\PrincipalBundle\Entity\Usuario;

class DefaultController extends Controller
{
	/**
	  * @Route("/", name="ec_propietario_homepage")
	  * @Template("ECPropietarioBundle:Default:index.html.twig")
	  */
	public function indexAction(){
		 return $this->render('ECPropietarioBundle:Default:index.html.twig');
	}
	
	 /**
	  * @Route("/perfil", name="ec_propietario_perfil")
	  * @Template("ECPropietarioBundle:Default:modificacion_datospersonales.html.twig")
	  */
    public function modificacion_perfilAction(Request $request)
    {
    		$propietario = $this->getUser();
    		
    		$form = $this ->createFormBuilder($propietario)
    				->add('nombre','text')
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
        	
        	return $this->render('ECPropietarioBundle:Default:modificacion_datospersonales.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    	
    }
    
    /**
	  * @Route("/contraseña", name="ec_propietario_contraseña")
	  * @Template("ECPropietarioBundle:Default:modificacion_contraseña.html.twig")
	  */
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
       				FROM ECPrincipalBundle:Usuario v
      				WHERE v.id = :id'
					)->setParameter('id', $this->getUser());
					$propietario = $query->getSingleResult();
					
					$encoder_old = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
					$clave_old = $encoder_old->encodePassword($form->get('pass')->getData(),$propietario->getSalt());
					
            	if($propietario->getPassword()==$clave_old){
						$propietario->setSalt(md5(time()));
            		$encoder_new = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
						$clave_new = $encoder_new->encodePassword($form->get('password')->getData(),$propietario->getSalt());
						$propietario->setPassword($clave_new);
						
   				 	$em->persist($propietario);
    					$em->flush();
    					
						$this->get('session')->getFlashBag()->add('notice','La contraseña ha sido actualizada.');
   				 	$this->get('session')->getFlashBag()->add('color','green');
   				 	return $this->redirect($this->generateUrl('ec_propietario_contraseña'));	
            	}else{    				     				 
    				 	$this->get('session')->getFlashBag()->add('notice','Contraseña no válida.');
   				 	$this->get('session')->getFlashBag()->add('color','red');
   				 	return $this->redirect($this->generateUrl('ec_propietario_contraseña'));	
        			}
    		}
    	
    		return $this->render('ECPropietarioBundle:Default:modificacion_contraseña.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
 	}
}
