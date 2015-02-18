<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\AdminFincas;
use EC\PrincipalBundle\Entity\Usuario;
use EC\PrincipalBundle\Entity\Role;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class AdminFincasController extends Controller
{  
	 private function setSecurePassword($entity) {
			$entity->setSalt(md5(time()));
			$encoder = new \Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder('sha512', false, 10);
			$password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
			$entity->setPassword($password);
	 }
	 
	 private function generar_usuario($nombre) {
	 		$nombre=strtolower($nombre);//Convierte a minusculas
	 		$nombre = preg_replace('/[.*-_;]/', '', $nombre);//Eliminamos caracteres especiales
	 		$vector=preg_split("/[\s,]+/",$nombre);//Divide en elementos de un vector
	 		
			$usuario=$vector[0].'.'.$vector[1];
			if($this->comprobar_usuario($usuario)){
				return $usuario;
			}else{
				$usuario=$vector[1].'.'.$vector[0];
				if($this->comprobar_usuario($usuario)){
					return $usuario;
				}else{
					$usuario=$vector[0].'_'.$vector[1];	
					if($this->comprobar_usuario($usuario)){
						return $usuario;
					}else{
						$usuario=$vector[1].'_'.$vector[0];
						if($this->comprobar_usuario($usuario)){
							return $usuario;
						}else{
							$generator = new SecureRandom();
							$random = bin2hex($generator->nextBytes(4));
							return $random;
						}
					}
				}
			}
	 }
	 
	 private function comprobar_usuario($nombre_usuario){
	 		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT u
       			FROM ECPrincipalBundle:Usuario u
      			WHERE u.user = :nombre_usuario'
			)->setParameters(array('nombre_usuario' => $nombre_usuario));
			
			try {
    				$comprobacion = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			return 1;
			}	
			return 0;
	 }
    
    /**
	  * @Route("/alta", name="ec_principal_alta_adminfincas")
	  * @Template("ECPrincipalBundle:AdminFincas:alta_adminfincas.html.twig")
	  */
    public function alta_adminfincasAction(Request $request)
    {
    		$adminfincas = new AdminFincas();
    		
    		$invalid_message=$this->get('translator')->trans('Las contraseñas deben coincidir');
    		$form = $this ->createFormBuilder($adminfincas)
    				->setAction($this->generateUrl('ec_principal_alta_adminfincas'))
    				->add('dni','text', array('label' => 'DNI','max_length' =>9))
    				->add('n_colegiado','text', array('label' => 'NºColegiado','max_length' =>9,'required' => false))
    				->add('nombre','text')
    				->add('apellidos','text')
    				->add('telefono','text', array('required' => true,'label' => 'Teléfono','max_length' =>9))
    				->add('fax','text',array('required' => false, 'max_length' =>9))
    				->add('email','email')
    				->add('direccion','text', array('label' => 'Dirección'))
    				->add('provincia','text')
    				->add('localidad','text')
    				->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => $invalid_message,
                'required' => true,
                'first_options'  => array('label' => 'Contraseña','max_length' =>9),
    				 'second_options' => array('label' => 'Confirmación','max_length' =>9),
    				))
    				->getForm();
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    				$num=$form->get('dni')->getData();
        			$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT b
       				FROM ECPrincipalBundle:AdminFincas b
      				WHERE b.dni = :dni'
					)->setParameters(array('dni' => $num));    			
    		
    				try{
    					$comprobacion=$query->getSingleResult();	
					} catch (\Doctrine\Orm\NoResultException $e) {
						$comprobacion=null;
					}
            	if($comprobacion){						
						$flash=$this->get('translator')->trans('Administrador de Fincas ya registrado.');
        				$this->get('session')->getFlashBag()->add('notice',$flash);
   					$this->get('session')->getFlashBag()->add('color','red');
   					return $this->redirect($this->generateUrl('ec_principal_alta_adminfincas'));
            	}else{    				     				 
						$this->setSecurePassword($adminfincas);
						$nombre_usuario=$this->generar_usuario($adminfincas->getNombre().' '.$adminfincas->getApellidos());
    					$adminfincas->setUser($nombre_usuario);
						$role=$this->getDoctrine()->getRepository('ECPrincipalBundle:Role')->findById('1');
						$adminfincas->setRole($role[0]);
						$role[0]->addUsuario($adminfincas);
    			
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($adminfincas);
   				 	$em->persist($role[0]);
   				 	$em->flush();
   				 	
   				 	$message = \Swift_Message::newInstance()
        				->setSubject('Alta EntreComunidades')
        				->setFrom('info.entrecomunidades@gmail.com')
        				->setTo($form->get('email')->getData())
        				->setContentType('text/html')
        				->setBody($this->renderView('ECPrincipalBundle:AdminFincas:email_alta.txt.twig', array('nombre'=>$form->get('nombre')->getData().' '.$form->get('apellidos')->getData(),'usuario'=>$form->get('dni')->getData(),'pass'=>$form->get('password')->getData())));
    					$this->get('mailer')->send($message);
						
						$flash=$this->get('translator')->trans('Alta realizada con éxito. En breves momentos recibirá un email con sus datos de acceso.');
        				$this->get('session')->getFlashBag()->add('notice',$flash);
   					$this->get('session')->getFlashBag()->add('color','green');
   					return $this->redirect($this->generateUrl('ec_principal_alta_adminfincas'));
        			}
        	}
        	
        	return $this->render('ECPrincipalBundle:AdminFincas:alta_adminfincas.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
    
    /**
	  * @Route("/adminfincas/perfil", name="ec_adminfincas_perfil")
	  * @Template("ECPrincipalBundle:AdminFincas:modificacion_datospersonales.html.twig")
	  */
    public function modificacion_perfilAction(Request $request)
    {
			$adminfincas = $this->getUser();
    		
    		$form = $this ->createFormBuilder($adminfincas)
    		    	->setAction($this->generateUrl('ec_adminfincas_perfil'))
    			   ->add('n_colegiado','text', array('label' => 'NºColegiado','required' => false))
    				->add('nombre','text')
    				->add('apellidos','text')
    				->add('telefono','text', array('label' => 'Teléfono'))
    				->add('fax','text', array('required' => false))
    				->add('email','email')
    				->add('direccion','text', array('label' => 'Dirección'))
    				->add('provincia','text')
    				->add('localidad','text')
    				->getForm();
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {				     				     			
    				 	$em = $this->getDoctrine()->getManager();
   				 	$em->persist($adminfincas);
   				 	$em->flush();
    			
    					$flash=$this->get('translator')->trans('Su perfil ha sido actualizado.');
        	      	$this->get('session')->getFlashBag()->add('notice',$flash);
        			 	$this->get('session')->getFlashBag()->add('color','green');
						return $this->redirect($this->generateUrl('ec_adminfincas_perfil'));
        	}
        	
        	return $this->render('ECPrincipalBundle:AdminFincas:modificacion_datospersonales.html.twig',
        	       		array('form' => $form->createView(),
        	      		));
    }
}