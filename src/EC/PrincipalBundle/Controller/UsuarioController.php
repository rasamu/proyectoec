<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Util\SecureRandom;

class UsuarioController extends Controller
{   
    /**
	  * @Route("/usuario/contraseña", name="ec_usuario_contraseña")
	  * @Template("ECPrincipalBundle:Usuario:modificacion_contraseña.html.twig")
	  */
    public function modificacion_contraseñaAction(Request $request)
    {
    		$form = $this->createFormBuilder()
    			->setAction($this->generateUrl('ec_usuario_contraseña'))
        		->add('pass', 'password', array('label' => 'Contraseña','max_length' =>9))
				->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Las contraseñas deben coincidir',
                'required' => true,
                'first_options'  => array('label' => 'Nueva Contraseña','max_length' =>9),
    				 'second_options' => array('label' => 'Confirmación','max_length' =>9),
    				))
       		->getForm();
 
    		$form->handleRequest($request);
 
    		if ($form->isValid()) {
       		 	$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT u
       				FROM ECPrincipalBundle:Usuario u
      				WHERE u.id = :id'
					)->setParameter('id', $this->getUser());
					$usuario = $query->getSingleResult();
					
					$encoder_old = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
					$clave_old = $encoder_old->encodePassword($form->get('pass')->getData(),$usuario->getSalt());
					
            	if($usuario->getPassword()==$clave_old){
            		$usuario->setPassword($form->get('password')->getData());
            		
						$ComprobacionesService=$this->get('usuario_service');
      				$ComprobacionesService->setSecurePassword($usuario);
						
   				 	$em->persist($usuario);
    					$em->flush();
    					
    					$flash=$this->get('translator')->trans('La contraseña ha sido cambiada.');
						$this->get('session')->getFlashBag()->add('notice',$flash);
   				 	$this->get('session')->getFlashBag()->add('color','green');
   				 	return $this->redirect($this->generateUrl('ec_usuario_contraseña'));	
            	}else{    				     				 
    				 	$flash=$this->get('translator')->trans('Contraseña no válida.');
    				 	$this->get('session')->getFlashBag()->add('notice',$flash);
   				 	$this->get('session')->getFlashBag()->add('color','red');
   				 	return $this->redirect($this->generateUrl('ec_usuario_contraseña'));	
        			}
    		}
    	
    		return $this->render('ECPrincipalBundle:Usuario:modificacion_contraseña.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
 	}
 	
 	/**
	  * @Route("/olvido/password", name="ec_principal_olvido_password")
	  * @Template("ECPrincipalBundle:Usuario:olvido_password.html.twig")
	  */
    public function olvido_passwordAction(Request $request)
    {   	
    		$form = $this->createFormBuilder()
    			->setAction($this->generateUrl('ec_principal_olvido_password'))
        		->add('email', 'email', array('label'=>'Email','required' => true))
        		->getForm();
        		
        	$form->handleRequest($request);
     		if ($form->isValid()) {
       		$email = $form->get('email')->getData();
       	 	
       	 	$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT a
      	 		FROM ECPrincipalBundle:AdminFincas a
      	 		WHERE a.email= :email'
				)->setParameters(array('email'=>$email));
				try {
    				$adminfincas = $query->getSingleResult();
				} catch (\Doctrine\Orm\NoResultException $e) {
        			$adminfincas=null;
				}
				
				if($adminfincas){
					$ComprobacionesService=$this->get('usuario_service');
      			$nuevo_password=$ComprobacionesService->generar_password();
      			
					$adminfincas->setPassword($nuevo_password);

					$ComprobacionesService=$this->get('usuario_service');
      			$ComprobacionesService->setSecurePassword($adminfincas);
      			
      			$em = $this->getDoctrine()->getManager();
   				$em->persist($adminfincas);
   				$em->flush();
					
					//Mandamos Email
					$message = \Swift_Message::newInstance()
        				->setSubject('Nuevo contraseña EntreComunidades')
        				->setFrom('info.entrecomunidades@gmail.com')
        				->setTo($email)
        				->setContentType('text/html')
        				->setBody($this->renderView('ECPrincipalBundle:Usuario:email_nuevo_password.txt.twig', array('nombre'=>$adminfincas->getNombre().' '.$adminfincas->getApellidos(),'usuario'=>$adminfincas->getUser(),'pass'=>$nuevo_password)));
    				$this->get('mailer')->send($message);
    				
    				$flash=$this->get('translator')->trans('Se le han enviado sus nuevos datos de acceso.');
        			$this->get('session')->getFlashBag()->add('notice', $flash);
   				$this->get('session')->getFlashBag()->add('color','green'); 
   				return $this->redirect($this->generateUrl('ec_principal_olvido_password'));  	
				}else{
					$flash=$this->get('translator')->trans('No existe ningún administrador registrado con el email'.' '.$email);
        			$this->get('session')->getFlashBag()->add('notice', $flash);
   				$this->get('session')->getFlashBag()->add('color','red'); 
   				return $this->redirect($this->generateUrl('ec_principal_olvido_password'));  	
				} 					 	
   		}
        		
        	return $this->render('ECPrincipalBundle:Usuario:olvido_password.html.twig', array(
            		'form' => $form->createView(),
       			));
    }
}