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
use FOS\ElasticaBundle\FOSElasticaBundle;

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
	  * @Route("/contactar", name="ec_buscador_contactar")
	  */
    public function contactarAction(Request $request)
    {
     		$request = $this->get('request');		
     		$nombre=$request->request->get('Nombre');
   		$mensaje=$request->request->get('Mensaje');
   		$email=$request->request->get('Email');
   		$id_anuncio=$request->request->get('idAnuncio');
   
   		if ($request->isXmlHttpRequest()) {
   			if($mensaje!="" and $email!="" and $nombre!=""){
   				$anuncio=$this->comprobar_anuncio($id_anuncio);
   				$email_anunciante=$anuncio->getUsuario()->getEmail();
   			
   				//Enviamos email
   				$message = \Swift_Message::newInstance()
        				->setSubject('Mensaje anuncio EntreComunidades')
        				->setFrom('info.entrecomunidades@gmail.com')
        				->setTo($email_anunciante)
        				->setContentType('text/html')
        				->setBody($this->renderView('ECPrincipalBundle:Anuncio:email_contacto_anuncio.txt.twig', array('anuncio'=>$anuncio,'nombre'=>$nombre,'mensaje'=>$mensaje, 'email'=>$email)));
    				$this->get('mailer')->send($message);
    					
   				$flash=$this->get('translator')->trans('Mensaje enviado con éxito.');
      			$return=array("responseCode"=>200,  "greeting"=>$flash);
   			}else{
   				$flash=$this->get('translator')->trans('El mensaje no se ha enviado correctamente.');
   		   	$return=array("responseCode"=>400, "greeting"=>$flash);
   			}
   		}else{
   			$flash=$this->get('translator')->trans('Error.');
   		   $return=array("responseCode"=>400, "greeting"=>$flash);
   		}

   		$return=json_encode($return);
  			return new Response($return,200,array('Content-Type'=>'application/json'));
    }
    
    /**
	  * @Route("/denunciar", name="ec_buscador_denunciar")
	  */
    public function denunciarAction(Request $request)
    {
     		$request = $this->get('request'); 		
     		$denuncia=$request->request->get('Denuncia');
   		$id_anuncio=$request->request->get('idAnuncio');
   		
   		if ($request->isXmlHttpRequest()) {
   			if($denuncia!=""){
   				$anuncio=$this->comprobar_anuncio($id_anuncio);
   				$usuario=$anuncio->getUsuario();
   				if($usuario->getRole()->getId()!='1'){//Anuncio escrito por propietario
   					$email=$anuncio->getComunidad()->getAdministrador()->getEmail();
   				}else{//Anuncio escrito por el administrador de la comunidad
   					$email='info.entrecomunidades@gmail.com';
   				}
   				//Enviamos email
   				$message = \Swift_Message::newInstance()
        				->setSubject('Denuncia anuncio EntreComunidades')
        				->setFrom('info.entrecomunidades@gmail.com')
        				->setTo($email)
        				->setContentType('text/html')
        				->setBody($this->renderView('ECPrincipalBundle:Anuncio:email_denuncia_anuncio.txt.twig', array('anuncio'=>$anuncio,'denuncia'=>$denuncia)));
    				$this->get('mailer')->send($message);
    					
   				$flash=$this->get('translator')->trans('Denuncia enviada con éxito.');
      			$return=array("responseCode"=>200,  "greeting"=>$flash);
   			}else{
   				$flash=$this->get('translator')->trans('Tienes que escribir el mensaje.');
   		   	$return=array("responseCode"=>400, "greeting"=>$flash);
   			}
   		}else{
   			$flash=$this->get('translator')->trans('Error.');
   		   $return=array("responseCode"=>400, "greeting"=>$flash);
   		}

   		$return=json_encode($return);
  			return new Response($return,200,array('Content-Type'=>'application/json'));
    }
	 
	 /**
	  * @Route("/", name="ec_buscador_anuncios")
	  * @Template("ECPrincipalBundle:Anuncio:buscador.html.twig")
	  */
    public function buscadorAction(Request $request)
    {
    		$results=null;
    		
			$defaultData = array('message' => 'Type your message here');
    		$form = $this->container
    			->get('form.factory')
    			->createNamedBuilder('form','form',null)
    			->setAction($this->generateUrl('ec_buscador_anuncios'))
        		->add('palabras', 'text',array('label'=>'Palabras','required' => false))
        		->add('categoria', 'entity', array(
            		'class' => 'ECPrincipalBundle:CategoriaAnuncios',
            		'property'=>'nombre',
            		'label' => 'Categoría',
            		'required' => false,
            		'empty_value' => 'Cualquier categoría'))
            ->add('province', 'entity', array(
            		'class' => 'ECPrincipalBundle:Province',
            		'property'=>'name',
            		'label' => 'Provincia',
            		'required' => false,
            		'empty_value' => 'En toda España'))
            ->add('orden', 'choice', array(
                'choices' => array(
                    'desc' => 'Fecha descendente',
                    'asc' => 'Fecha ascendente'
                ),
                'data' => 'desc'))
        		->getForm();
        	
        	$form_contacto = $this->container
    			->get('form.factory')
    			->createNamedBuilder('form_contacto','form',null)
    			->setAction($this->generateUrl('ec_buscador_contactar'))
        		->add('email','email', array('label'=>'Email','required' => true))
        		->add('nombre','text', array('label'=>'Nombre','required' => true))
        		->add('mensaje', 'textarea',array('label'=>'Mensaje','required' => true))
        		->getForm();
        		
        	$form_denuncia = $this->container
    			->get('form.factory')
    			->createNamedBuilder('form_denuncia','form',null)
    			->setAction($this->generateUrl('ec_buscador_denunciar'))
        		->add('denuncia', 'textarea',array('label'=>'Denuncia','required' => true))
        		->getForm();

     		$form=$form->handleRequest($request);		
 
       	$palabras=$form->get('palabras')->getData();
       	$categoria=$form->get('categoria')->getData();
       	$orden_fecha=$form->get('orden')->getData();
       	$province=$form->get('province')->getData();

			$finder = $this->container->get('fos_elastica.finder.search.anuncio');
			$boolQuery = new \Elastica\Query\Bool();
			
			//Filtramos por categoria
			if($categoria!=null){
				$categoryQuery = new \Elastica\Query\Term();
				$categoryQuery->setTerm('categoria.id', $categoria->getId());
				$boolQuery->addMust($categoryQuery);
			}
			
			//Filtramos por provincia
			if($province!=null){
				$provinceQuery = new \Elastica\Query\Term();
				$provinceQuery->setTerm('comunidad.city.province.id', $province->getId());
				$boolQuery->addMust($provinceQuery);	
			}
			
			//Filtramos por palabras
			if($palabras!=null){
				/*$fieldQuery1 = new \Elastica\Query\Match();
				$fieldQuery1->setFieldQuery('titulo', $palabras);
				$boolQuery->addShould($fieldQuery1);*/
				
				$fieldQuery2 = new \Elastica\Query\Match();
				$fieldQuery2->setFieldQuery('descripcion', $palabras);
				$boolQuery->addMust($fieldQuery2);
			}else{
				$fieldQuery = new \Elastica\Query\MatchAll();
				$boolQuery->addShould($fieldQuery);
			}
		
			$finalQuery = new \Elastica\Query($boolQuery);
			//Ordenamos
			$finalQuery->setSort(array('fecha' => array('order'=>$orden_fecha)));
			$results = $finder->find($finalQuery);
			
			//Paginamos
			$anuncios  = $this->get('knp_paginator')->paginate(
         	$results,
         	$request->query->get('page', 1)/*page number*/,
         	2/*limit per page*/
    		);
				
    		return $this->render('ECPrincipalBundle:Anuncio:buscador.html.twig',array('form' => $form->createView(),'form_contacto'=>$form_contacto->createView(),'form_denuncia'=>$form_denuncia->createView(),'anuncios'=>$anuncios,));
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
    		$flash=$this->get('translator')->trans('Para publicar anuncios debe añadir primero su Teléfono e Email.');
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