<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use EC\PrincipalBundle\Entity\Usuario;
use EC\PrincipalBundle\Entity\Anuncio;
use EC\PrincipalBundle\Form\Type\AnuncioType;

class AnuncioController extends Controller
{
	 private function comprobar_anuncio($id){
	 		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT a
       			FROM ECPrincipalBundle:Anuncio a
      			WHERE a.id = :id'
			)->setParameters(array('id' => $id));
			
			try {
    				$anuncio = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}	

    		return $anuncio;
	 }
	 
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
	
	/**
	  * @Route("/nuevo/anuncio/", name="ec_nuevo_anuncio")
	  * @Template("ECPrincipalBundle:Anuncio:nuevo_anuncio.html.twig")
	  */
	public function nuevo_anuncioAction(Request $request, $cif)
	{ 		
		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    		$comunidad=$this->comprobar_comunidad($cif);
    	}else{
    		$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();	
    	}		
		
		//Comprobamos que el usuario tiene registrado su telefono e email
    	if($this->getUser()->getTelefono()==null || $this->getUser()->getEmail()==null){
    		$flash=$this->get('translator')->trans('Para publicar anuncios debe añadir primero su Teléfono e Email');
        	$this->get('session')->getFlashBag()->add('notice',$flash);
   		$this->get('session')->getFlashBag()->add('color','red');
   		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
   			return $this->redirect($this->generateUrl('ec_adminfincas_perfil'));
   		}else{
   			return $this->redirect($this->generateUrl('ec_propietario_perfil'));
   		}
		}else{
			//NUEVO ANUNCIO
			$anuncio = new Anuncio();
    		$form=$this->createForm(new AnuncioType(),$anuncio);
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {					
    				$categoria=$form->get('categoria')->getData();
					
					$anuncio->setCategoria($categoria);
					$anuncio->setUsuario($this->getUser());
					$anuncio->setComunidad($comunidad);
					$this->getUser()->addAnuncio($anuncio);
					$categoria->addAnuncio($anuncio);
					$comunidad->addAnuncio($anuncio);
					
    				$em = $this->getDoctrine()->getManager();
					$em->persist($anuncio);
					$em->persist($this->getUser());
   				$em->persist($categoria);
   				$em->persist($comunidad);
   				$em->flush();		
    			
					$flash=$this->get('translator')->trans('Nuevo anuncio registrado con éxito.');
					$this->get('session')->getFlashBag()->add('notice',$flash);
        			$this->get('session')->getFlashBag()->add('color','green');
        			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
						return $this->redirect($this->generateUrl('ec_listado_mis_anuncios', array('cif'=>$comunidad->getCif())));
					}else{
						return $this->redirect($this->generateUrl('ec_listado_mis_anuncios'));
					}
        	}
			
			return $this->render('ECPrincipalBundle:Anuncio:nuevo_anuncio.html.twig',array('form' => $form->createView(),'comunidad'=>$comunidad));
       }				 	
	}
	
	/**
	  * @Route("/anuncios/listado/", name="ec_listado_mis_anuncios")
	  * @Template("ECPrincipalBundle:Anuncio:listado_mis_anuncios.html.twig")
	  */
	public function listado_mis_anunciosAction($cif)
	{ 		
		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    		$comunidad=$this->comprobar_comunidad($cif);
    		
    		$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT a
					FROM ECPrincipalBundle:Anuncio a
					WHERE a.usuario = :usuario and a.comunidad=:comunidad'
				)->setParameters(array('usuario'=>$this->getUser(),'comunidad'=>$comunidad,));
			$mis_anuncios = $query->getResult();	
			
			$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT a
					FROM ECPrincipalBundle:Anuncio a
					WHERE a.usuario != :usuario and a.comunidad=:comunidad'
				)->setParameters(array('usuario'=>$this->getUser(),'comunidad'=>$comunidad,));	
			$anuncios_comunidad = $query->getResult();	
    		
    		return $this->render('ECPrincipalBundle:Anuncio:listado_mis_anuncios.html.twig',array('mis_anuncios'=>$mis_anuncios,'anuncios_comunidad'=>$anuncios_comunidad,'comunidad'=>$comunidad)); 
    	}else{
    		
    		if($this->get('security.context')->isGranted('ROLE_PRESIDENTE') or $this->get('security.context')->isGranted('ROLE_VICEPRESIDENTE')){
    			$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();
    			
    			$mis_anuncios=$this->getUser()->getAnuncios();	
			
				$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT a
					FROM ECPrincipalBundle:Anuncio a
					WHERE a.usuario != :usuario and a.comunidad=:comunidad'
				)->setParameters(array('usuario'=>$this->getUser(),'comunidad'=>$comunidad,));	
				$anuncios_comunidad = $query->getResult();
    			
    			return $this->render('ECPrincipalBundle:Anuncio:listado_mis_anuncios.html.twig',array('mis_anuncios'=>$mis_anuncios,'anuncios_comunidad'=>$anuncios_comunidad)); 
    			
    		}else{
    			$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();
    			$mis_anuncios=$this->getUser()->getAnuncios();
    			
    			return $this->render('ECPrincipalBundle:Anuncio:listado_mis_anuncios.html.twig',array('mis_anuncios'=>$mis_anuncios)); 
    		}
    	}				 	
	}

	 /**
	  * @Route("/eliminar/anuncio/", name="ec_eliminar_anuncio")
	  */
	public function eliminarAction($id)
	{ 		
		$anuncio=$this->comprobar_anuncio($id);
		$comunidad=$anuncio->getComunidad();		
    	$categoria=$anuncio->getCategoria();
    	$usuario=$anuncio->getUsuario();
		
		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
			if($comunidad->getAdministrador()!=$this->getUser()){
    			throw new AccessDeniedException();
    		}	
		}else{
    		if($usuario!=$this->getUser()){
    			throw new AccessDeniedException();
    		}	
    	}
    	
    	$usuario->removeAnuncio($anuncio);
    	$categoria->removeAnuncio($anuncio);
    	$comunidad->removeAnuncio($anuncio);
    	$em = $this->getDoctrine()->getManager();
    	$em->remove($anuncio);
    	$em->persist($usuario);
    	$em->persist($categoria);
    	$em->persist($comunidad);
		$em->flush();
    			
    	$flash=$this->get('translator')->trans('Anuncio eliminado con éxito.');
    	$this->get('session')->getFlashBag()->add('notice',$flash);
   	$this->get('session')->getFlashBag()->add('color','green');
		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
			return $this->redirect($this->generateUrl('ec_listado_mis_anuncios', array('cif'=>$comunidad->getCif())));
		}else{
			return $this->redirect($this->generateUrl('ec_listado_mis_anuncios'));
		}
	}
	
	/**
	  * @Route("/adminfincas/estadisticas/anuncios/categorias", name="ec_adminfincas_estadisticas_anuncios_categorias")
	  * @Template("ECPrincipalBundle:Anuncio:estadisticas_anuncios_categorias.html.twig")
	  */
    public function estadisticasAnunciosCategoriasAction()
    {   			
			/*Contamos los anuncios de todas las comunidades que administra el administrador por categorias*/
    		$em = $this->getDoctrine()->getManager();	
			$query = $em->createQuery(
    			'SELECT cat.nombre,(SELECT COUNT(a) FROM ECPrincipalBundle:Anuncio a
    			WHERE a.categoria=cat and a.comunidad IN
				(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.administrador= :admin)) as total
				FROM ECPrincipalBundle:CategoriaAnuncios cat'
			)->setParameters(array('admin'=>$this->getUser()));			
			$categorias = $query->getResult();		  				
    		
    		return $this->render('ECPrincipalBundle:Anuncio:estadisticas_anuncios_categorias.html.twig',
					array('categorias'=>$categorias,
					));
    }
}