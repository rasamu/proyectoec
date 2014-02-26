<?php

namespace EC\VecinoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use EC\VecinoBundle\Entity\Vecino;

class DefaultController extends Controller
{
    public function modificacion_perfilAction(Request $request)
    {
    	$vecino = $this->getUser();
    		
    		$form = $this ->createFormBuilder($vecino)
    				->add('nombre','text')
    				->add('apellidos','text')
    				->add('telefono','integer', array('label' => 'Teléfono'))
    				->add('email','text')
    				->add('portal','text', array('label' => 'Portal'))
    				->add('piso','text', array('label' => 'Piso'))
    				->getForm();
    		
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    				 	 $em = $this->getDoctrine()->getManager();
               	
   					 $em->persist($vecino);
   					 $em->flush();
    			
							return $this->render('ECVecinoBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Modificación realizada con éxito.',
        	      		));  				
        	}
        	
        	return $this->render('ECVecinoBundle:Default:modificacion_datospersonales.html.twig',
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
       				FROM ECVecinoBundle:Vecino v
      				WHERE v.dni = :dni'
					)->setParameter('dni', $this->getUser());
					$vecino = $query->getSingleResult();
					
					$encoder_old = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
					$clave_old = $encoder_old->encodePassword($form->get('pass')->getData(),$vecino->getSalt());
					
            	if($vecino->getPassword()==$clave_old){
						$vecino->setSalt(md5(time()));
            		$encoder_new = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
						$clave_new = $encoder_new->encodePassword($form->get('password')->getData(),$vecino->getSalt());
						$vecino->setPassword($clave_new);
						
   				 	$em->persist($vecino);
    					$em->flush();
    					
						return $this->render('ECVecinoBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Cambio de contraseña realizado con éxito.',
        	      		));
            	}else{    				     				 
    				 	return $this->render('ECVecinoBundle:Default:mensaje.html.twig',
        	       		array('mensaje' => 'Contraseña no válida.',
        	      		));
        			}
    		}
    	
    		return $this->render('ECVecinoBundle:Default:modificacion_contraseña.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
 	}
}
