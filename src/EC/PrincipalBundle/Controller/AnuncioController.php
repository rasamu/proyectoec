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
use EC\PrincipalBundle\Entity\Imagen;
use EC\PrincipalBundle\Entity\CategoriaAnuncios;
use EC\PrincipalBundle\Form\Type\AnuncioType;
use EC\PrincipalBundle\Form\Type\ImagenType;
use Gregwar\Image\GarbageCollect;

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
	  * @Route("/", name="ec_buscador_anuncios")
	  * @Template("ECPrincipalBundle:Anuncio:buscador.html.twig")
	  */
    public function buscadorAction()
    {
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    			'SELECT a
				FROM ECPrincipalBundle:Anuncio a'
			);	
			$anuncios = $query->getResult();
		
    		 			
    		return $this->render('ECPrincipalBundle:Anuncio:buscador.html.twig',array('anuncios'=>$anuncios,));
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
    		//$formView->getChild('fotos')->set('full_name', 'form_anuncio[fotos][]');
    				
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
					
					$imagenes=$anuncio->getImagenes();
					$orden=1;
					foreach($imagenes as $imagen){
						if($imagen->getFile()!=null){
							$anuncio->AddImagene($imagen);
							$imagen->SetAnuncio($anuncio);
							$imagen->setOrden($orden);
							$em->persist($imagen);	
							$orden++;
						}else{
							$anuncio->removeImagene($imagen);
							$em->remove($imagen);	
						}
					}
					
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
	  * @Route("/modificar/anuncio/{id}", name="ec_modificar_anuncio")
	  * @Template("ECPrincipalBundle:Anuncio:modificar_anuncio.html.twig")
	  */
	public function modificarAction(Request $request, $id)
	{ 		
		$anuncio=$this->comprobar_anuncio($id);
		$comunidad=$anuncio->getComunidad();
    	$usuario=$anuncio->getUsuario();	
	
    	if($usuario!=$this->getUser()){
    		throw new AccessDeniedException();
    	}	

    	$form=$this->createForm(new AnuncioType(),$anuncio);
    	$formView=$form->createView();
    				
    	$form->handleRequest($request);
    			
    	if ($form->isValid()) {					
    			$categoria=$form->get('categoria')->getData();
					
				$anuncio->setCategoria($categoria);
				$categoria->addAnuncio($anuncio);
					
				$em = $this->getDoctrine()->getManager();
				$em->persist($anuncio);
   			$em->persist($categoria);
   			$em->flush();		
    			
				$flash=$this->get('translator')->trans('Anuncio modificado con éxito.');
				$this->get('session')->getFlashBag()->add('notice',$flash);
        		$this->get('session')->getFlashBag()->add('color','green');
        		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
					return $this->redirect($this->generateUrl('ec_listado_mis_anuncios', array('cif'=>$comunidad->getCif())));
				}else{
					return $this->redirect($this->generateUrl('ec_listado_mis_anuncios'));
				}
        	}
			
		return $this->render('ECPrincipalBundle:Anuncio:modificar_anuncio.html.twig',array('form' => $formView, 'anuncio'=>$anuncio,'comunidad'=>$comunidad));				 	
	}
	
	/**
	  * @Route("/modificar/anuncio/imagenes/{id}", name="ec_modificar_anuncio_imagenes")
	  * @Template("ECPrincipalBundle:Anuncio:modificar_anuncio_imagenes.html.twig")
	  */
	public function modificarImagenesAction(Request $request, $id)
	{ 		
		$anuncio=$this->comprobar_anuncio($id);
		$comunidad=$anuncio->getComunidad();
    	$usuario=$anuncio->getUsuario();
		
    	if($usuario!=$this->getUser()){
    		throw new AccessDeniedException();
    	}	
    	
		$imagen = new Imagen();
    	$form=$this->createForm(new ImagenType(),$imagen);
    				
    	$form->handleRequest($request);
    			
    	if ($form->isValid()) {									
				$em = $this->getDoctrine()->getManager();	
				$anuncio->AddImagene($imagen);
				$imagen->setAnuncio($anuncio);
				$imagen->setOrden($anuncio->getImagenes()->count());
				$em->persist($imagen);
				$em->persist($anuncio);		
   			$em->flush();		
    			
				$flash=$this->get('translator')->trans('Imagen añadida con éxito.');
				$this->get('session')->getFlashBag()->add('notice',$flash);
        		$this->get('session')->getFlashBag()->add('color','green');
				return $this->redirect($this->generateUrl('ec_modificar_anuncio_imagenes', array('id'=>$id)));
      }
      
	   $imagenes_anuncio=$anuncio->getImagenes();		
		return $this->render('ECPrincipalBundle:Anuncio:modificar_anuncio_imagenes.html.twig',array('form' => $form->createView(), 'imagenes'=>$imagenes_anuncio, 'anuncio'=>$anuncio,'comunidad'=>$comunidad));				 	
	}
	
	public function modificarImagenesOrdenAction(Request $request)
	{ 		
	  	$imagenes = json_decode($request->get('json'));
	  	$id_anuncio = json_decode($request->get('anuncio'));
	  	 	
    	if (!$imagenes || !$id_anuncio) {
        	return new Response(null, 200);
    	}
    	
		$anuncio=$this->comprobar_anuncio($id_anuncio);
		$usuario=$anuncio->getUsuario();
    	if($usuario!=$this->getUser()){
    		throw new AccessDeniedException();
    	}
     	
     	$orden=1;
     	$em = $this->getDoctrine()->getManager();
		foreach($imagenes as $imagen){
			$query = $em->createQuery(
				'UPDATE ECPrincipalBundle:Imagen i 
				SET i.orden = :orden 
				WHERE i.id= :id_imagen and i.anuncio= :id_anuncio'
			)->setParameters(array('orden'=>$orden,'id_imagen'=>$imagen,'id_anuncio'=>$id_anuncio));
			$query->execute();
			
			$orden++;
		}
		
      $response = array("code" => 100, "success" => true);
  		return new Response(json_encode($response)); 
	}
	
	/**
	  * @Route("/eliminar/imagen/{id_imagen}/anuncio/{id}", name="ec_eliminar_imagen_anuncio")
	  */
	public function eliminarImagenAnuncioAction($id_imagen, $id)
	{ 		
		$anuncio=$this->comprobar_anuncio($id);
		$comunidad=$anuncio->getComunidad();
    	$usuario=$anuncio->getUsuario();
    	
    	if($usuario!=$this->getUser()){
    		throw new AccessDeniedException();
    	}
    	
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
    		'SELECT i
      	 FROM ECPrincipalBundle:Imagen i
      	 WHERE i.id= :id and i.anuncio= :anuncio'
		)->setParameters(array('id'=> $id_imagen,'anuncio'=>$anuncio));
 
		try {
    		$imagen = $query->getSingleResult();
		} catch (\Doctrine\Orm\NoResultException $e) {
        	throw new AccessDeniedException();
		}
    	
		$anuncio->removeImagene($imagen);
		$em->remove($imagen);	
		$em->flush();
    			
    	$flash=$this->get('translator')->trans('Imagen eliminada con éxito.');
    	$this->get('session')->getFlashBag()->add('notice',$flash);
   	$this->get('session')->getFlashBag()->add('color','green');
		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
			return $this->redirect($this->generateUrl('ec_modificar_anuncio_imagenes', array('cif'=>$comunidad->getCif(),'id'=>$id)));
		}else{
			return $this->redirect($this->generateUrl('ec_modificar_anuncio_imagenes',array('id'=>$id)));
		}
	}

	 /**
	  * @Route("/eliminar/anuncio/{id}", name="ec_eliminar_anuncio")
	  */
	public function eliminarAction($id)
	{ 		
		$anuncio=$this->comprobar_anuncio($id);
		$comunidad=$anuncio->getComunidad();		
    	$categoria=$anuncio->getCategoria();
    	$usuario=$anuncio->getUsuario();
    	$imagenes=$anuncio->getImagenes();
		
		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
			if($comunidad->getAdministrador()!=$this->getUser()){
    			throw new AccessDeniedException();
    		}	
		}else{
    		if($usuario!=$this->getUser()){
    			throw new AccessDeniedException();
    		}	
    	}
    	
    	$em = $this->getDoctrine()->getManager();
		foreach($imagenes as $imagen){
			$anuncio->removeImagene($imagen);
			$em->remove($imagen);	
		}
		//Eliminamos la cache de imagenes reducidas con una antiguedad de más de 30 días
		GarbageCollect::dropOldFiles($this->getRequest()->server->get('DOCUMENT_ROOT') .'/uploads/anuncios/cache', 30, true);
    	$usuario->removeAnuncio($anuncio);
    	$categoria->removeAnuncio($anuncio);
    	$comunidad->removeAnuncio($anuncio);
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