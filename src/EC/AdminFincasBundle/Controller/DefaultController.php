<?php

namespace EC\AdminFincasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\AdminFincasBundle\Entity\AdminFincas;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class DefaultController extends Controller
{  
   /**
	  * @Route("/", name="ec_adminfincas_homepage")
	  * @Template("ECAdminFincasBundle:Default:index.html.twig")
	  */
	public function indexAction(){
		 return $this->render('ECAdminFincasBundle:Default:index.html.twig');
	}	
    
    /**
	  * @Route("/perfil", name="ec_adminfincas_perfil")
	  * @Template("ECAdminFincasBundle:Default:modificacion_datospersonales.html.twig")
	  */
    public function modificacion_perfilAction(Request $request)
    {
			$adminfincas = $this->getUser();
    		
    		$form = $this ->createFormBuilder($adminfincas)
    			   ->add('n_colegiado','text', array('label' => 'NºColegiado','required' => false))
    				->add('nombre','text')
    				->add('apellidos','text')
    				->add('telefono','integer', array('label' => 'Teléfono'))
    				->add('fax','integer', array('required' => false))
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
    			
        	      	$this->get('session')->getFlashBag()->add('notice','Su perfil ha sido actualizado.');
        			 	$this->get('session')->getFlashBag()->add('color','green');
						return $this->redirect($this->generateUrl('ec_adminfincas_perfil'));
        	}
        	
        	return $this->render('ECAdminFincasBundle:Default:modificacion_datospersonales.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    /**
	  * @Route("/contraseña", name="ec_adminfincas_contraseña")
	  * @Template("ECAdminFincasBundle:Default:modificacion_contraseña.html.twig")
	  */
    public function modificacion_contraseñaAction(Request $request)
    {
    		$form = $this->createFormBuilder()
        		->add('pass', 'password', array('label' => 'Contraseña actual','max_length' =>9))
				->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Las dos contraseñas deben coincidir',
                'required' => true,
                'first_options'  => array('label' => 'Nueva Contraseña','max_length' =>9),
    				 'second_options' => array('label' => 'Confirmación Contraseña','max_length' =>9),
    				))
       		->getForm();
 
    		$form->handleRequest($request);
 
    		if ($form->isValid()) {
       		 	$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT v
       				FROM ECAdminFincasBundle:AdminFincas v
      				WHERE v.dni = :dni'
					)->setParameter('dni', $this->getUser());
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
    					
						$this->get('session')->getFlashBag()->add('notice','Su contraseña ha sido actualizada.');
        			 	$this->get('session')->getFlashBag()->add('color','green');
						return $this->redirect($this->generateUrl('ec_adminfincas_contraseña'));
            	}else{    				     				 
    				 	$this->get('session')->getFlashBag()->add('notice','Contraseña no válida.');
        			 	$this->get('session')->getFlashBag()->add('color','red');
						return $this->redirect($this->generateUrl('ec_adminfincas_contraseña'));
        			}
    		}
    	
    		return $this->render('ECAdminFincasBundle:Default:modificacion_contraseña.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
 	}

}
