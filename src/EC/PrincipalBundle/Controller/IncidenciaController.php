<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\Usuario;
use EC\PrincipalBundle\Entity\Incidencia;
use EC\PrincipalBundle\Entity\Actuacion;
use EC\PrincipalBundle\Entity\Estado;
use EC\PrincipalBundle\Entity\Categoria;
use EC\PrincipalBundle\Entity\Privacidad;
use EC\PrincipalBundle\Form\Type\IncidenciaType;
use EC\PrincipalBundle\Form\Type\ActuacionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Util\SecureRandom;

class IncidenciaController extends Controller
{  
	private function comprobar_comunidad($cif) {
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT c
       			FROM ECPrincipalBundle:Comunidad c
      			WHERE c.cif = :cif and c.administrador = :admin'
			)->setParameters(array('cif' => $cif, 'admin' => $this->getUser()));
			
			try {
    				$comunidad = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}			
    		return $comunidad;	
	 }
	 
	 private function comprobar_incidencia($id) {
			$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT i
       			FROM ECPrincipalBundle:Incidencia i
      			WHERE i.id = :id'
			)->setParameters(array('id' => $id,));
			
			try {
    				$incidencia = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}
			
			//Comprobamos que la incidencia pertenezca a un usuario de una comunidad cuyo administrador de fincas sea el usuario activo
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
					$admin=$incidencia->getUsuario()->getPropiedad()->getBloque()->getComunidad()->getAdministrador();
					if($admin!=$this->getUser()){
						throw new AccessDeniedException();
					}
			}else{
				//Comprobamos que el presidente o vicepresidente pertenezca a la misma comunidad de la incidencia
				if($this->get('security.context')->isGranted('ROLE_PRESIDENTE') or $this->get('security.context')->isGranted('ROLE_VICEPRESIDENTE')){
					$comunidad=$incidencia->getUsuario()->getPRopiedad()->getBloque()->getComunidad();
					if($comunidad!=$this->getUser()->getPropiedad()->getBloque()-getComunidad()){
						throw new AccessDeniedException();
					}
				}else{
					//Comprobamos que la incidencia pertenezca al vecino activo o sea pública en el bloque o comunidad
					if($this->get('security.context')->isGranted('ROLE_VECINO')){
						$bloque=$this->getUser()->getPropiedad()->getBloque();
						$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();
						$usuario=$this->getUser();
						
						$privado_usuario=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('1');
						$privado_bloque=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('2');
        				$privado_comunidad=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('3');
        				
        				$usuario_incidencia=$incidencia->getUsuario();
        				$bloque_incidencia=$usuario_incidencia->getPropiedad()->getBloque();
        				$comunidad_incidencia=$bloque_incidencia->getComunidad();
        				$privacidad_incidencia=$incidencia->getPrivacidad();
        				
						if(($comunidad_incidencia!=$comunidad) or //Es de otra comunidad
							($usuario_incidencia!=$usuario and $privacidad_incidencia==$privado_usuario[0]) or //Es de otro usuario y es privado
							($bloque_incidencia!=$bloque and $privacidad_incidencia!=$privado_comunidad[0]) or
							($bloque_incidencia==$bloque and ($privacidad_incidencia==$privado_usuario[0] and $usuario_incidencia!=$usuario)))
							{ 
							throw new AccessDeniedException();
						}
					}else{
							throw new AccessDeniedException();
					}
				}
			}					
    		return $incidencia;	
	 }
    
    /**
	  * @Route("/propietario/incidencia/nueva", name="ec_propietario_nueva_incidencia")
	  * @Template("ECPrincipalBundle:Incidencias:nueva_incidencia.html.twig")
	  */
    public function nueva_incidenciaAction(Request $request)
    {
    		$incidencia = new Incidencia();
    		$form=$this->createForm(new IncidenciaType(),$incidencia);
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {					
    				$categoria=$form->get('categoria')->getData();
					$estado=$this->getDoctrine()->getRepository('ECPrincipalBundle:Estado')->findById('1');
					$privacidad=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('1');
					
					$incidencia->setCategoria($categoria);
					$incidencia->setEstado($estado[0]);
					$incidencia->setPrivacidad($privacidad[0]);
					$incidencia->setUsuario($this->getUser());
					$categoria->addIncidencia($incidencia);
					$estado[0]->addIncidencia($incidencia);
					$privacidad[0]->addIncidencia($incidencia);
					
    				$em = $this->getDoctrine()->getManager();
					$em->persist($incidencia);
   				$em->persist($categoria);
   				$em->persist($estado[0]);
   				$em->persist($privacidad[0]);
   				$em->flush();
   				
   				$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();
   				$administrador=$comunidad->getAdministrador();
   				
   				/*Correo al administrador*/
   				$message = \Swift_Message::newInstance()
        			->setSubject('Incidencia Comunidad: '.$comunidad->getCodigo())
        			->setFrom('info@proyectoec.hol.es')
        			->setTo($administrador->getEmail())
        			->setContentType('text/html')
        			->setBody($this->renderView('ECPrincipalBundle:Incidencia:email_nueva_incidencia.txt.twig', array('categoria'=>$categoria->getNombre(),'descripcion'=>$incidencia->getDescripcion())));
    				$this->get('mailer')->send($message);
    			
    			
					$flash=$this->get('translator')->trans('Incidencia notificada con éxito.');
					$this->get('session')->getFlashBag()->add('notice',$flash);
        			$this->get('session')->getFlashBag()->add('color','green');
					return $this->redirect($this->generateUrl('ec_listado_incidencias'));
        	}

			return $this->render('ECPrincipalBundle:Incidencia:nueva_incidencia.html.twig',
						array('form' => $form->createView()
						));
    }
    
    /**
	  * @Route("/incidencias/listado", name="ec_listado_incidencias")
	  * @Template("ECPrincipalBundle:Incidencias:listado_incidencias.html.twig")
	  */
    public function listado_incidenciasAction($cif=null)
    {
    		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS') or $this->get('security.context')->isGranted('ROLE_PRESIDENTE') or $this->get('security.context')->isGranted('ROLE_VICEPRESIDENTE')){
    			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    				$comunidad=$this->comprobar_comunidad($cif);
    			}else{
    				$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();	
    			}
    			
    			/*Buscamos las incidencias de la comunidad*/
    			$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT i
					FROM ECPrincipalBundle:Incidencia i
					WHERE i.usuario IN
					(SELECT u FROM ECPrincipalBundle:Usuario u WHERE u.propiedad IN
					(SELECT p FROM ECPrincipalBundle:Propiedad p WHERE p.bloque IN
					(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad)))'
				)->setParameters(array('comunidad'=>$comunidad,));
				
				$incidencias = $query->getResult();		
    		}else{
    			$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();
    			$bloque=$this->getUser()->getPropiedad()->getBloque();
    			$privacidad_bloque_publica=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('2');
        		$privacidad_comunidad_publica=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('3');
        				
    			/*Buscamos las incidencias del usuario privadas y de la comunidad o bloque publicas*/
    			$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT i
					FROM ECPrincipalBundle:Incidencia i
					WHERE (i.privacidad= :privacidad_comunidad_publica and i.usuario IN
						(SELECT u FROM ECPrincipalBundle:Usuario u WHERE u.propiedad IN
						(SELECT p FROM ECPrincipalBundle:Propiedad p WHERE p.bloque IN
						(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad))))
					or (i.privacidad= :privacidad_bloque_publica and i.usuario IN
						(SELECT s FROM ECPrincipalBundle:Usuario s WHERE s.propiedad IN
						(SELECT t FROM ECPrincipalBundle:Propiedad t WHERE t.bloque= :bloque)))
					or (i.usuario= :usuario)'
				)->setParameters(array('comunidad'=>$comunidad,'bloque'=>$bloque,'usuario'=>$this->getUser(),'privacidad_comunidad_publica'=>$privacidad_comunidad_publica,'privacidad_bloque_publica'=>$privacidad_bloque_publica,));
				
				$incidencias = $query->getResult();	
				
    		}
    		
			return $this->render('ECPrincipalBundle:Incidencia:listado_incidencias.html.twig',
					array('incidencias'=>$incidencias,'comunidad'=>$comunidad,
					));
    }
    
    /**
	  * @Route("/incidencia/{id}", name="ec_incidencia")
	  * @Template("ECPrincipalBundle:Incidencias:ver_incidencia.html.twig")
	  */
    public function ver_incidenciaAction($id, Request $request)
    {
    		$incidencia=$this->comprobar_incidencia($id);
    		$estado=$incidencia->getEstado();//lo almacenamos pq se cambia con el form
    		$privacidad=$incidencia->getPrivacidad();//lo almacenamos pq se cambia con el form
		   $comunidad=$incidencia->getUsuario()->getPropiedad()->getBloque()->getComunidad();
    		$actuaciones=$incidencia->getActuaciones();
    		
    		$form_estado = $this ->createFormBuilder($incidencia,array('csrf_protection' => false))
    				->add('estado','entity', array(
    						'class'=>'ECPrincipalBundle:Estado',
         				'property'=>'nombre',
         				'label'=>'Estado',
         				'query_builder'=>function(EntityRepository $er){
         					return $er->createQueryBuilder('e');
         		    	}
         		 ))->getForm();
        
         $form_privacidad = $this ->createFormBuilder($incidencia,array('csrf_protection' => false))
    				->add('privacidad','entity', array(
    						'class'=>'ECPrincipalBundle:Privacidad',
         				'property'=>'nombre',
         				'label'=>'Privacidad',
         				'query_builder'=>function(EntityRepository $er){
         					return $er->createQueryBuilder('p');
         		    	}
         		 ))->getForm();
    		
    		$actuacion = new Actuacion();
    		$form=$this->createForm(new ActuacionType(),$actuacion);
    			
		  if ($this->getRequest()->isMethod('POST')) {
		  	$form_estado->bind($this->getRequest());
		  	$form->bind($this->getRequest());
		  	$form_privacidad->bind($this->getRequest());
    			if ($form->isValid()) {		
					$actuacion->setIncidencia($incidencia);
					$actuacion->setUsuario($this->getUser());
					$incidencia->AddActuacione($actuacion);
					$this->getUser()->AddActuacione($actuacion);
					$incidencia->setEstado($estado);//Volvemos a asignar el estado inicial
					$incidencia->setPrivacidad($privacidad);//Volvemos a asignar la privacidad inicial
					
    				$em = $this->getDoctrine()->getManager();
    				$em->persist($incidencia);
    				$em->persist($actuacion);
    				$em->persist($this->getUser());
   				$em->flush();
   			
   				/*Correo*/
   				/*$message = \Swift_Message::newInstance()
        			->setSubject('Incidencia Comunidad: '.$comunidad->getCodigo())
        			->setFrom('info@proyectoec.hol.es')
        			->setTo($administrador->getEmail())
        			->setContentType('text/html')
        			->setBody($this->renderView('ECPrincipalBundle:Incidencia:email_nueva_incidencia.txt.twig', array('categoria'=>$categoria->getNombre(),'descripcion'=>$incidencia->getDescripcion())));
    				$this->get('mailer')->send($message);*/   			
    			
					$flash=$this->get('translator')->trans('Mensaje enviado correctamente.');
					$this->get('session')->getFlashBag()->add('notice',$flash);
        			$this->get('session')->getFlashBag()->add('color','green');
					return $this->redirect($this->generateUrl('ec_incidencia',array('id'=>$incidencia->getId())));
    			}
    			if($form_estado->isValid()){
					$estado=$this->getDoctrine()
        				->getRepository('ECPrincipalBundle:Estado')
        				->findById($form_estado->get('estado')->getData());				
        				
					$estado_anterior=$incidencia->getEstado();
					$incidencia->setEstado($estado[0]);
					$incidencia->setPrivacidad($privacidad);//Volvemos a asignar la privacidad inicial
					$estado_anterior->removeIncidencia($incidencia);
					$estado[0]->addIncidencia($incidencia);	
					
					//Guardamos el cambio de estado como una nueva actuacion
					$actuacion->setIncidencia($incidencia);
					$incidencia->AddActuacione($actuacion);
					$actuacion->setUsuario($this->getUser());			
					$this->getUser()->AddActuacione($actuacion);
					$actuacion->setMensaje('Estado ha cambiado a '.$estado[0]->getNombre());
					
    				$em = $this->getDoctrine()->getManager();
					$em->persist($incidencia);
					$em->persist($actuacion);
					$em->persist($this->getUser());
   				$em->persist($estado[0]);
   				$em->persist($estado_anterior);
   				$em->flush();
   			
   				/*Correo*/
   				/*$message = \Swift_Message::newInstance()
        			->setSubject('Incidencia Comunidad: '.$comunidad->getCodigo())
        			->setFrom('info@proyectoec.hol.es')
        			->setTo($administrador->getEmail())
        			->setContentType('text/html')
        			->setBody($this->renderView('ECPrincipalBundle:Incidencia:email_nueva_incidencia.txt.twig', array('categoria'=>$categoria->getNombre(),'descripcion'=>$incidencia->getDescripcion())));
    				$this->get('mailer')->send($message);*/   			
    			
					$flash=$this->get('translator')->trans('Estado cambiado con éxito.');
					$this->get('session')->getFlashBag()->add('notice',$flash);
        			$this->get('session')->getFlashBag()->add('color','green');
					return $this->redirect($this->generateUrl('ec_incidencia',array('id'=>$incidencia->getId())));		
    			}
    			if($form_privacidad->isValid()){
    					$privacidad=$this->getDoctrine()
        				->getRepository('ECPrincipalBundle:Privacidad')
        				->findById($form_privacidad->get('privacidad')->getData());
        				
        			   $privacidad_anterior=$incidencia->getPrivacidad();
        			   $privacidad_anterior->removeIncidencia($incidencia);
    					$incidencia->setPrivacidad($privacidad[0]);
    					$incidencia->setEstado($estado);//Volvemos a asignar el estado inicial
    					$privacidad[0]->addIncidencia($incidencia);
					
    					$em = $this->getDoctrine()->getManager();
						$em->persist($incidencia);
						$em->persist($privacidad[0]);
   					$em->persist($privacidad_anterior);
   					$em->flush();		
   					
   					$flash=$this->get('translator')->trans('La privacidad ha sido cambiada con éxito.');
						$this->get('session')->getFlashBag()->add('notice',$flash);
        				$this->get('session')->getFlashBag()->add('color','green');
						return $this->redirect($this->generateUrl('ec_incidencia',array('id'=>$incidencia->getId())));	
    			}
    		}
			return $this->render('ECPrincipalBundle:Incidencia:ver_incidencia.html.twig',
					array('incidencia'=>$incidencia, 'actuaciones'=>$actuaciones, 'form' => $form->createView(), 'form_estado' => $form_estado->createView(), 'form_privacidad' => $form_privacidad->createView(),'comunidad'=>$comunidad
					));
    }
    
}