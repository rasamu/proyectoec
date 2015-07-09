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
   				$ComprobacionesService=$this->get('comprobaciones_service');
      			$anuncio=$ComprobacionesService->comprobar_anuncio($id_anuncio); 
      			
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
   				$ComprobacionesService=$this->get('comprobaciones_service');
      			$anuncio=$ComprobacionesService->comprobar_anuncio($id_anuncio);
      			
   				$email=$anuncio->getComunidad()->getAdministrador()->getEmail();

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
    public function buscadorAction(Request $request, $id_anuncio)
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
            ->add('mostrar', 'choice', array(
                'choices' => array(
                    '2' => '2 Anuncio/Página',
                    '10' => '10 Anuncios/Página',
                    '25' => '25 Anuncios/Página',
                    '50' => '50 Anuncios/Página',                                      
                ),
                'data' => '2'))
            ->add('orden', 'choice', array(
                'choices' => array(
                    'desc' => 'Fecha descendente',
                    'asc' => 'Fecha ascendente'
                ),
                'data' => 'desc'))
            ->setMethod('GET')
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
       	$anuncios_por_pagina=$form->get('mostrar')->getData();
       	
       	if($id_anuncio==null){
       	
       		//SERVIDOR
       		/*if($categoria!=null and $province!=null){
       			$em = $this->getDoctrine()->getManager();
						$query = $em->createQuery(
    						'SELECT a
							FROM ECPrincipalBundle:Anuncio a
							WHERE a.categoria = :categoria and a.comunidad in
							(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.city in
							(SELECT cu FROM ECPrincipalBundle:City cu WHERE cu.province = :province)) order by a.fecha ASC'
						)->setParameters(array('categoria'=>$categoria, 'province'=>$province));
					$results = $query->getResult();
				}else{
					if($categoria==null and $province==null){
						$em = $this->getDoctrine()->getManager();
						$query = $em->createQuery(
    						'SELECT a
							FROM ECPrincipalBundle:Anuncio a order by a.fecha ASC'
						);
						$results = $query->getResult();
					}else{
						if($categoria!=null){
							$em = $this->getDoctrine()->getManager();
							$query = $em->createQuery(
    							'SELECT a
								FROM ECPrincipalBundle:Anuncio a
								WHERE a.categoria = :categoria order by a.fecha ASC'
							)->setParameters(array('categoria'=>$categoria));
							$results = $query->getResult();
						}else{
							$em = $this->getDoctrine()->getManager();
							$query = $em->createQuery(
    							'SELECT a
								FROM ECPrincipalBundle:Anuncio a
								WHERE a.comunidad in
								(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.city in
								(SELECT cu FROM ECPrincipalBundle:City cu WHERE cu.province=:province)) order by a.fecha ASC'
							)->setParameters(array('province'=>$province));
							$results = $query->getResult();
						}
					}
				}*/

				$finder = $this->container->get('fos_elastica.finder.search.anuncio');
				$boolQuery = new \Elastica\Query\Bool();
				$boolQuery2 = new \Elastica\Query\Bool();
					
				//Filtramos por palabras
				if($palabras!=null){
					$fieldQuery1 = new \Elastica\Query\Match();
					$fieldQuery1->setFieldQuery('titulo', $palabras);
					$boolQuery2->addShould($fieldQuery1);
				
					$fieldQuery2 = new \Elastica\Query\Match();
					$fieldQuery2->setFieldQuery('descripcion', $palabras);
					$boolQuery2->addShould($fieldQuery2);
					
					$fieldQuery3 = new \Elastica\Query\Match();
					$fieldQuery3->setFieldQuery('comunidad.city.name', $palabras);
					$boolQuery2->addShould($fieldQuery3);
				}else{
					$fieldQuery = new \Elastica\Query\MatchAll();
					$boolQuery2->addShould($fieldQuery);
				}
				
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
				
				$boolQuery->addMust($boolQuery2);
				
				$finalQuery = new \Elastica\Query($boolQuery);
			
				$finalQuery->setHighlight(array(
					'pre_tags' => array('<strong class="highlight_anuncio">'),
					'post_tags' => array('</strong>'),
					'fields' => array(
						'descripcion' => array(
							'fragment_size' => 200,
							'number_of_fragments' => 1,
						),
						'titulo' => array(
							'fragment_size' => 200,
							'number_of_fragments' => 1,
						),
						'comunidad.city.name' => array(
							'fragment_size' => 200,
							'number_of_fragments' => 1,
						)
					),
				));
			
				//Ordenamos
				$finalQuery->setSort(array('fecha' => array('order'=>$orden_fecha)));
				$hybridResults = $finder->findHybrid($finalQuery);
	
				//$highlights=null;			
			
				foreach($hybridResults as $hybridResult){
					$results[]=$hybridResult->getResult();
					//$results[]=$hybridResult->getTransformed();
				}
			
				if($results!=null){			
					//Paginamos
					$anuncios  = $this->get('knp_paginator')->paginate(
         			$results,
         			$request->query->get('page', 1)/*page number*/,
         			$anuncios_por_pagina/*limit per page*/
    				);
    			}else{
    				$anuncios=null;	
    			}
    		}else{
       		$em = $this->getDoctrine()->getManager();
    			$query = $em->createQuery(
    				'SELECT a FROM ECPrincipalBundle:Anuncio a WHERE a.id = :id_anuncio'
				)->setParameters(array('id_anuncio'=>$id_anuncio));    			
    		
    			try{
    				$anuncio=$query->getSingleResult();	
					$anuncios[0]=$anuncio;	
				} catch (\Doctrine\Orm\NoResultException $e) {
					$anuncios=null;
				}
    		}
				
    		return $this->render('ECPrincipalBundle:Anuncio:buscador.html.twig',array('form' => $form->createView(),'form_contacto'=>$form_contacto->createView(),'form_denuncia'=>$form_denuncia->createView(),'anuncios'=>$anuncios,'id_anuncio'=>$id_anuncio));
    }
	
	/**
	  * @Route("/nuevo/anuncio/{id_comunidad}", name="ec_nuevo_anuncio")
	  * @Template("ECPrincipalBundle:Anuncio:nuevo_anuncio.html.twig")
	  */
	public function nuevo_anuncioAction(Request $request, $id_comunidad)
	{ 		
		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
    	}else{
    		$comunidad=$this->getUser()->getBloque()->getComunidad();	
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
    			
					$flash=$this->get('translator')->trans('Nuevo anuncio creado con éxito. A continuación, puede adjuntar las imágenes del anuncio.');
					$this->get('session')->getFlashBag()->add('notice',$flash);
        			$this->get('session')->getFlashBag()->add('color','green');
					return $this->redirect($this->generateUrl('ec_modificar_anuncio_imagenes', array('id'=>$anuncio->getId())));
        	}
			
			return $this->render('ECPrincipalBundle:Anuncio:nuevo_anuncio.html.twig',array('form' => $form->createView(),'comunidad'=>$comunidad));
       }				 	
	}
	
	/**
	  * @Route("/anuncios/listado/{id_comunidad}", name="ec_listado_mis_anuncios")
	  * @Template("ECPrincipalBundle:Anuncio:listado_mis_anuncios.html.twig")
	  */
	public function listado_mis_anunciosAction($id_comunidad)
	{ 		
		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
    		
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
    			$comunidad=$this->getUser()->getBloque()->getComunidad();
    			
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
    			$comunidad=$this->getUser()->getBloque()->getComunidad();
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
		$ComprobacionesService=$this->get('comprobaciones_service');
      $anuncio=$ComprobacionesService->comprobar_anuncio($id);
      
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
					return $this->redirect($this->generateUrl('ec_listado_mis_anuncios', array('id_comunidad'=>$comunidad->getId())));
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
		$ComprobacionesService=$this->get('comprobaciones_service');
      $anuncio=$ComprobacionesService->comprobar_anuncio($id);
      
		$comunidad=$anuncio->getComunidad();
    	$usuario=$anuncio->getUsuario();
		
    	if($usuario!=$this->getUser()){
    		throw new AccessDeniedException();
    	}	
    	
		$imagen = new Imagen();
    	$form=$this->createForm(new ImagenType(),$imagen);
    				
    	$form->handleRequest($request);
    			
    	if ($form->isValid()) {							
				//Guardamos
				$em = $this->getDoctrine()->getManager();	
				$anuncio->AddImagene($imagen);
				$imagen->setAnuncio($anuncio);
				$imagen->setOrden($anuncio->getImagenes()->count());
				$em->persist($imagen);
				$em->persist($anuncio);		
   			$em->flush();
   			
   			//Añadimos marca de agua
				// Cargar la estampa y la foto para aplicarle la marca de agua
				$estampa = imagecreatefrompng($this->container->get('templating.helper.assets')->getUrl('images/watermark.png'));
				$im = imagecreatefromjpeg($imagen->getWebPath());

				// Establecer los márgenes para la estampa y obtener el alto/ancho de la imagen de la estampa
				$margen_dcho = 25;
				$margen_inf = 25;
				$sx = imagesx($estampa);
				$sy = imagesy($estampa);

				// Copiar la imagen de la estampa sobre nuestra foto usando los índices de márgen y el
				// ancho de la foto para calcular la posición de la estampa. 
				imagecopy($im, $estampa, imagesx($im) - $sx - $margen_dcho, imagesy($im) - $sy - $margen_inf, 0, 0, imagesx($estampa), imagesy($estampa));	 			
				imagejpeg($im,$imagen->getWebPath());
				
				imagedestroy($im);	
				
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
    	
		$ComprobacionesService=$this->get('comprobaciones_service');
      $anuncio=$ComprobacionesService->comprobar_anuncio($id_anuncio);
      
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
		$ComprobacionesService=$this->get('comprobaciones_service');
      $anuncio=$ComprobacionesService->comprobar_anuncio($id);
      			
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
		
		//Eliminamos la cache de imagenes reducidas con una antiguedad de más de 30 días
		GarbageCollect::dropOldFiles($this->getRequest()->server->get('DOCUMENT_ROOT') .'/uploads/anuncios/cache', 30, true);
    			
    	$flash=$this->get('translator')->trans('Imagen eliminada con éxito.');
    	$this->get('session')->getFlashBag()->add('notice',$flash);
   	$this->get('session')->getFlashBag()->add('color','green');
		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
			return $this->redirect($this->generateUrl('ec_modificar_anuncio_imagenes', array('id_comunidad'=>$comunidad->getId(),'id'=>$id)));
		}else{
			return $this->redirect($this->generateUrl('ec_modificar_anuncio_imagenes',array('id'=>$id)));
		}
	}

	 /**
	  * @Route("/eliminar/anuncio/{id}", name="ec_eliminar_anuncio")
	  */
	public function eliminarAction($id)
	{ 		
		$ComprobacionesService=$this->get('comprobaciones_service');
      $anuncio=$ComprobacionesService->comprobar_anuncio($id);
      
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
		/*foreach($imagenes as $imagen){
			$anuncio->removeImagene($imagen);
			$em->remove($imagen);	
		}*/
		
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
			return $this->redirect($this->generateUrl('ec_listado_mis_anuncios', array('id_comunidad'=>$comunidad->getId())));
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