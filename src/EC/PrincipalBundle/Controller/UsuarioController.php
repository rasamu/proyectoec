<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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
    					'SELECT u
       				FROM ECPrincipalBundle:Usuario u
      				WHERE u.id = :id'
					)->setParameter('id', $this->getUser());
					$usuario = $query->getSingleResult();
					
					$encoder_old = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
					$clave_old = $encoder_old->encodePassword($form->get('pass')->getData(),$usuario->getSalt());
					
            	if($usuario->getPassword()==$clave_old){
						$usuario->setSalt(md5(time()));
            		$encoder_new = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
						$clave_new = $encoder_new->encodePassword($form->get('password')->getData(),$usuario->getSalt());
						$usuario->setPassword($clave_new);
						
   				 	$em->persist($usuario);
    					$em->flush();
    					
						$this->get('session')->getFlashBag()->add('notice','La contraseña ha sido actualizada.');
   				 	$this->get('session')->getFlashBag()->add('color','green');
   				 	return $this->redirect($this->generateUrl('ec_usuario_contraseña'));	
            	}else{    				     				 
    				 	$this->get('session')->getFlashBag()->add('notice','Contraseña no válida.');
   				 	$this->get('session')->getFlashBag()->add('color','red');
   				 	return $this->redirect($this->generateUrl('ec_usuario_contraseña'));	
        			}
    		}
    	
    		return $this->render('ECPrincipalBundle:Usuario:modificacion_contraseña.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
 	}
}