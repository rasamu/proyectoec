<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use EC\PrincipalBundle\Entity\Servicio;
use EC\PrincipalBundle\Entity\Valoracion;
use EC\PrincipalBundle\Form\Type\ServicioType;
use EC\PrincipalBundle\Form\Type\ValoracionType;
use FOS\ElasticaBundle\FOSElasticaBundle;
use Ps\PdfBundle\Annotation\Pdf;

class ServicioController extends Controller
{   
    /**
	  * @Route("/comunidad/servicios/listado/{cif}", name="ec_listado_servicios")
	  * @Template("ECPrincipalBundle:Servicio:listado_servicios.html.twig")
	  */
    public function listadoAction($cif)
    {
	    if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif); 
    	 }else{
    		$comunidad=$this->getUser()->getBloque()->getComunidad();	
    	 }		
			
       $servicios=$comunidad->getServicios();
			
       return $this->render('ECPrincipalBundle:Servicio:listado_servicios.html.twig',array(
        	'servicios' => $servicios, 'comunidad' =>$comunidad
       ));
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{cif}/servicios/nuevo", name="ec_adminfincas_nuevo_servicio")
	  * @Template("ECPrincipalBundle:Servicio:nuevo_servicio.html.twig")
	  */
    public function nuevoAction(Request $request, $cif)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif); 
         
         $servicio = new Servicio();
    		$form=$this->createForm(new ServicioType(), $servicio);
    						
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {
    			$comprobacion=$ComprobacionesService->comprobar_servicio($form->get('cif')->getData());
    			if($comprobacion==null){
    			   $cuidad=$form->get('city')->getData();
    				$servicio->addComunidade($comunidad);
    				$comunidad->addServicio($servicio);
    				$cuidad->addServicio($servicio); 
    				$servicio->setCity($cuidad);
    				  			      	
    				$em = $this->getDoctrine()->getManager();
   				$em->persist($comunidad);
   				$em->persist($cuidad);
   				$em->persist($servicio);
   				$em->flush();
    				
    				$flash=$this->get('translator')->trans('Nuevo servicio creado con éxito.');
        			$this->get('session')->getFlashBag()->add('notice',$flash);
   				$this->get('session')->getFlashBag()->add('color','green');
   				return $this->redirect($this->generateUrl('ec_listado_servicios', array('cif'=>$comunidad->getCif())));
   			}else{
   				$flash=$this->get('translator')->trans('El servicio ya existe.');
        			$this->get('session')->getFlashBag()->add('notice',$flash);
   				$this->get('session')->getFlashBag()->add('color','red');
   				return $this->redirect($this->generateUrl('ec_listado_servicios', array('cif'=>$comunidad->getCif())));	
   			}
   		}
			
        	return $this->render('ECPrincipalBundle:Servicio:nuevo_servicio.html.twig',array(
        		'comunidad' =>$comunidad, 'form' => $form->createView(),
        	));
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{cif}/servicios/buscador", name="ec_adminfincas_buscador_servicios")
	  * @Template("ECPrincipalBundle:Servicio:buscador.html.twig")
	  */
    public function buscadorAction(Request $request, $cif)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif); 
      	
      	$results=null;		

			$defaultData = array('message' => 'Type your message here');
    		$form = $this->container
    			->get('form.factory')
    			->createNamedBuilder('form','form',null,array('csrf_protection' => false))
    			->setAction($this->generateUrl('ec_adminfincas_buscador_servicios', array('cif'=>$comunidad->getCif())))
        		->add('nombre', 'text',array('label'=>'Nombre','required' => false))
        		->add('categoria', 'entity', array(
            		'class' => 'ECPrincipalBundle:CategoriaServicios',
            		'property'=>'nombre',
            		'label' => 'Categoría',
            		'required' => false,
            		'empty_value' => 'Cualquier categoría'))
            ->add('province', 'entity', array(
            		'class' => 'ECPrincipalBundle:Province',
            		'property'=>'name',
            		'label' => 'Provincia',
            		'required' => false,
            		'empty_value' => 'En toda España',
						'data' => $comunidad->getCity()->getProvince()))
            ->add('mostrar', 'choice', array(
                'choices' => array(
                    '2' => '2 Servicios/Página',
                    '10' => '10 Servicios/Página',
                    '25' => '25 Servicios/Página',
                ),
                'data' => '2'))            
            ->setMethod('GET')
        		->getForm();    
        		
        	$form=$form->handleRequest($request);		
        
        	$nombre=$form->get('nombre')->getData();
       	$categoria=$form->get('categoria')->getData();
       	$province=$form->get('province')->getData();
       	$servicios_por_pagina=$form->get('mostrar')->getData(); 
       	
       	//SERVIDOR
       	/*if($categoria!=null and $province!=null){
       		$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT a
						FROM ECPrincipalBundle:Servicio a
						WHERE a.categoria = :categoria and a.city in
						(SELECT cu FROM ECPrincipalBundle:City cu WHERE cu.province = :province) order by a.fecha ASC'
					)->setParameters(array('categoria'=>$categoria, 'province'=>$province));
				$results = $query->getResult();
			}else{
				if($categoria==null and $province==null){
					$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT a
						FROM ECPrincipalBundle:Servicio a order by a.fecha ASC'
					);
					$results = $query->getResult();
				}else{
					if($categoria!=null){
						$em = $this->getDoctrine()->getManager();
						$query = $em->createQuery(
    						'SELECT a
							FROM ECPrincipalBundle:Servicio a
							WHERE a.categoria = :categoria order by a.fecha ASC'
						)->setParameters(array('categoria'=>$categoria));
						$results = $query->getResult();
					}else{
						$em = $this->getDoctrine()->getManager();
						$query = $em->createQuery(
    						'SELECT a
							FROM ECPrincipalBundle:Servicio a
							WHERE a.city in
							(SELECT cu FROM ECPrincipalBundle:City cu WHERE cu.province=:province) order by a.fecha ASC'
						)->setParameters(array('province'=>$province));
						$results = $query->getResult();
					}
				}
			}*/
       	
       	$finder = $this->container->get('fos_elastica.finder.search.servicio');
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
				$provinceQuery->setTerm('city.province.id', $province->getId());
				$boolQuery->addMust($provinceQuery);	
			}
			
			//Filtramos por nombre
			if($nombre!=null){			
				$fieldQuery2 = new \Elastica\Query\Match();
				$fieldQuery2->setFieldQuery('nombre', $nombre);
				$boolQuery->addMust($fieldQuery2);
			}else{
				$fieldQuery = new \Elastica\Query\MatchAll();
				$boolQuery->addShould($fieldQuery);
			}
		
			$finalQuery = new \Elastica\Query($boolQuery);
			
			$finalQuery->setHighlight(array(
				'pre_tags' => array('<strong class="highlight_servicio">'),
				'post_tags' => array('</strong>'),
				'fields' => array(
					'nombre' => array(
						'fragment_size' => 200,
						'number_of_fragments' => 1,
					)
				),
			));

			$hybridResults = $finder->findHybrid($finalQuery);
			
			foreach($hybridResults as $hybridResult){
				$results[]=$hybridResult->getResult();
			}
			
			if($results!=null){			
				//Paginamos
				$servicios  = $this->get('knp_paginator')->paginate(
         		$results,
         		$request->query->get('page', 1)/*page number*/,
         		$servicios_por_pagina/*limit per page*/
    			);
    		}else{
    			$servicios=null;	
    		}
			
        	return $this->render('ECPrincipalBundle:Servicio:buscador.html.twig',array(
        		'form' => $form->createView(),'servicios' => $servicios, 'comunidad' =>$comunidad
        	));
    }
    
    /**
	  * @Route("/adminfincas/comprobar/servicio", name="ec_adminfincas_comprobar_servicio")
	  */
    public function comprobarAction(Request $request)
    {
     		$request = $this->get('request');		
     		$cif_servicio=$request->request->get('cif_servicio');
     		$cif_comunidad=$request->request->get('cif_comunidad');
   
   		if ($request->isXmlHttpRequest()) {
   			$ComprobacionesService=$this->get('comprobaciones_service');
      		$servicio=$ComprobacionesService->comprobar_servicio($cif_servicio); 
      		$comunidad=$ComprobacionesService->comprobar_comunidad($cif_comunidad);  
   			if($servicio!=null){
   						
   				$nombre=$servicio->getNombre();
   				$url=$this->generateUrl('ec_adminfincas_ver_servicio', array('cif'=>$comunidad->getCif(), 'id'=>$servicio->getCif()));
      			$return=array("responseCode"=>200,  "nombre"=>$nombre, "url"=>$url);
   			}else{
   		   	$return=array("responseCode"=>400);
   			}
   		}else{
   		   $return=array("responseCode"=>400);
   		}

   		$return=json_encode($return);
  			return new Response($return,200,array('Content-Type'=>'application/json'));
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{cif}/servicios/{id}", name="ec_adminfincas_ver_servicio")
	  * @Template("ECPrincipalBundle:Servicio:ver_servicio.html.twig")
	  */
    public function verAction($cif,$id, Request $request)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
    		$comunidad=$ComprobacionesService->comprobar_comunidad($cif); 
      	$servicio=$ComprobacionesService->comprobar_servicio($id); 
      	
      	$valoracion=$ComprobacionesService->comprobar_valoracion($this->getUser(),$servicio);
      	
      	if($valoracion!=null){
      		$form=$this->createForm(new ValoracionType(),$valoracion);
      	}else{
      		$valoracion = new Valoracion();
    			$form=$this->createForm(new ValoracionType(),$valoracion);
      	}
			
        	$form->handleRequest($request);
        	if ($form->isValid()) {				
        	
				$valoracion->setAdminFincas($this->getUser());
      		$valoracion->setServicio($servicio);
      		$valoracion->setFecha();
      		$servicio->addValoracione($valoracion);
      		$this->getUser()->addValoracione($valoracion);
      		
      		$em = $this->getDoctrine()->getManager();
      		$em->persist($valoracion);
				$em->persist($servicio);
				$em->persist($this->getUser());
   			$em->flush();
   			
   			$flash=$this->get('translator')->trans('Valoración realizada con éxito.');
				$this->get('session')->getFlashBag()->add('notice',$flash);
        		$this->get('session')->getFlashBag()->add('color','green');
				return $this->redirect($this->generateUrl('ec_adminfincas_ver_servicio',array('cif'=>$comunidad->getCif(),'id'=>$servicio->getCif())));
      	}
      	
      	$results=$servicio->getValoraciones();
      	
      	if($results!=null){			
				//Paginamos
				$valoraciones  = $this->get('knp_paginator')->paginate(
         		$results,
         		$request->query->get('page', 1)/*page number*/,
         		5/*limit per page*/
    			);
    		}else{
    			$valoraciones=null;	
    		}
      	
        	return $this->render('ECPrincipalBundle:Servicio:ver_servicio.html.twig',array(
        		'servicio' => $servicio, 'comunidad' => $comunidad, 'form' => $form->createView(),'valoraciones'=>$valoraciones
        	));
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{cif_comunidad}/servicios/añadir/{cif_servicio}", name="ec_adminfincas_añadir_servicio")
	  * @Template("ECPrincipalBundle:Servicio:ver_servicio.html.twig")
	  */
    public function añadirAction($cif_comunidad,$cif_servicio)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
    		$comunidad=$ComprobacionesService->comprobar_comunidad($cif_comunidad); 
      	$servicio=$ComprobacionesService->comprobar_servicio($cif_servicio);
      	
    		$servicio->addComunidade($comunidad);
    		$comunidad->addServicio($servicio);
    				  			      	
    		$em = $this->getDoctrine()->getManager();
   		$em->persist($comunidad);
   		$em->persist($servicio);
   		$em->flush();
   		
   		$servicios=$comunidad->getServicios();
      	
      	$flash=$this->get('translator')->trans('Servicio añadido con éxito.');
        	$this->get('session')->getFlashBag()->add('notice',$flash);
   		$this->get('session')->getFlashBag()->add('color','green'); 
			
        	return $this->redirect($this->generateUrl('ec_listado_servicios', array('cif'=>$comunidad->getCif())));	
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{cif_comunidad}/servicios/quitar/{cif_servicio}", name="ec_adminfincas_quitar_servicio")
	  * @Template("ECPrincipalBundle:Servicio:ver_servicio.html.twig")
	  */
    public function quitarAction($cif_comunidad,$cif_servicio)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
    		$comunidad=$ComprobacionesService->comprobar_comunidad($cif_comunidad); 
      	$servicio=$ComprobacionesService->comprobar_servicio($cif_servicio);
      	
    		$servicio->removeComunidade($comunidad);
    		$comunidad->removeServicio($servicio);
    				  			      	
    		$em = $this->getDoctrine()->getManager();
   		$em->persist($comunidad);
   		$em->persist($servicio);
   		$em->flush();
   		
   		$servicios=$comunidad->getServicios();
      	
      	$flash=$this->get('translator')->trans('Servicio quitado con éxito.');
        	$this->get('session')->getFlashBag()->add('notice',$flash);
   		$this->get('session')->getFlashBag()->add('color','green'); 
			
         return $this->redirect($this->generateUrl('ec_listado_servicios', array('cif'=>$comunidad->getCif())));	
    }
    
    /**
     * @Pdf()
     * @Route("/comunidad/servicios/listado/pdf/{cif}", name="ec_listado_servicios_pdf")
     * @Template("ECPrincipalBundle:Servicio:listado_pdf.html.twig")
     */
    public function listado_pdfAction($cif)
    {
    	if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif); 
    	 }else{
    		$comunidad=$this->getUser()->getBloque()->getComunidad();	
    	 }		
      	 
    	$servicios=$comunidad->getServicios();    
    	$format = $this->get('request')->get('_format');
    	        
    	$filename = "servicios_".$comunidad->getCodigo().".pdf";
    	$response= $this->render(sprintf('ECPrincipalBundle:Servicio:listado_pdf.%s.twig', $format), array(
        		'servicios' => $servicios, 'comunidad' => $comunidad
    		));
    		
    	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		return $response;
    }
    
    /**
	  * @Route("/comunidad/servicios/listado/csv/{cif}", name="ec_listado_servicios_csv")
	  * @Template("ECPrincipalBundle:Servicio:listado_csv.html.twig")
	  */
    public function listado_csvAction($cif) {
    		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    			$ComprobacionesService=$this->get('comprobaciones_service');
      		$comunidad=$ComprobacionesService->comprobar_comunidad($cif); 
    		}else{
    			$comunidad=$this->getUser()->getBloque()->getComunidad();	
    	 	}		
      	
    		$servicios=$comunidad->getServicios();   
    	
			$filename = "servicios_".$comunidad->getCodigo().".csv";
	
			$response = $this->render('ECPrincipalBundle:Servicio:listado_csv.html.twig', array('servicios' => $servicios));
			$response->headers->set('Content-Type', 'text/csv');

			$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	}

}