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
	  * @Route("/comunidad/servicios/listado/{id_comunidad}", name="ec_listado_servicios")
	  * @Template("ECPrincipalBundle:Servicio:listado_servicios.html.twig")
	  */
    public function listadoAction($id_comunidad)
    {
	    if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
    	 }else{
    		$comunidad=$this->getUser()->getBloque()->getComunidad();	
    	 }		
			
       $servicios=$comunidad->getServicios();
			
       return $this->render('ECPrincipalBundle:Servicio:listado_servicios.html.twig',array(
        	'servicios' => $servicios, 'comunidad' =>$comunidad
       ));
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/servicios/nuevo", name="ec_adminfincas_nuevo_servicio")
	  * @Template("ECPrincipalBundle:Servicio:nuevo_servicio.html.twig")
	  */
    public function nuevoAction(Request $request, $id_comunidad)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
         
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
   				return $this->redirect($this->generateUrl('ec_listado_servicios', array('id_comunidad'=>$comunidad->getId())));
   			}else{
   				$flash=$this->get('translator')->trans('El servicio ya existe.');
        			$this->get('session')->getFlashBag()->add('notice',$flash);
   				$this->get('session')->getFlashBag()->add('color','red');
   				return $this->redirect($this->generateUrl('ec_listado_servicios', array('id_comunidad'=>$comunidad->getId())));	
   			}
   		}
			
        	return $this->render('ECPrincipalBundle:Servicio:nuevo_servicio.html.twig',array(
        		'comunidad' =>$comunidad, 'form' => $form->createView(),
        	));
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/servicios/buscador", name="ec_adminfincas_buscador_servicios")
	  * @Template("ECPrincipalBundle:Servicio:buscador.html.twig")
	  */
    public function buscadorAction(Request $request, $id_comunidad)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
      	
      	$results=null;		

			$defaultData = array('message' => 'Type your message here');
    		$form = $this->container
    			->get('form.factory')
    			->createNamedBuilder('form','form',null,array('csrf_protection' => false))
    			->setAction($this->generateUrl('ec_adminfincas_buscador_servicios', array('id_comunidad'=>$comunidad->getId())))
        		->add('palabras', 'text',array('label'=>'Palabras','required' => false))
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
        
        	$palabras=$form->get('palabras')->getData();
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
						(SELECT cu FROM ECPrincipalBundle:City cu WHERE cu.province = :province)'
					)->setParameters(array('categoria'=>$categoria, 'province'=>$province));
				$results = $query->getResult();
			}else{
				if($categoria==null and $province==null){
					$em = $this->getDoctrine()->getManager();
					$query = $em->createQuery(
    					'SELECT a
						FROM ECPrincipalBundle:Servicio a'
					);
					$results = $query->getResult();
				}else{
					if($categoria!=null){
						$em = $this->getDoctrine()->getManager();
						$query = $em->createQuery(
    						'SELECT a
							FROM ECPrincipalBundle:Servicio a
							WHERE a.categoria = :categoria'
						)->setParameters(array('categoria'=>$categoria));
						$results = $query->getResult();
					}else{
						$em = $this->getDoctrine()->getManager();
						$query = $em->createQuery(
    						'SELECT a
							FROM ECPrincipalBundle:Servicio a
							WHERE a.city in
							(SELECT cu FROM ECPrincipalBundle:City cu WHERE cu.province=:province)'
						)->setParameters(array('province'=>$province));
						$results = $query->getResult();
					}
				}
			}*/
       	
       	$finder = $this->container->get('fos_elastica.finder.search.servicio');
			$boolQuery = new \Elastica\Query\Bool();
			$boolQuery2 = new \Elastica\Query\Bool();
			
			//Filtramos por provincia
			if($province!=null){
				$provinceQuery = new \Elastica\Query\Term();
				$provinceQuery->setTerm('city.province.id', $province->getId());
				$boolQuery->addMust($provinceQuery);	
			}
			
			//Filtramos por categoria
			if($categoria!=null){
				$categoryQuery = new \Elastica\Query\Term();
				$categoryQuery->setTerm('categoria.id', $categoria->getId());
				$boolQuery->addMust($categoryQuery);
			}
			
			//Filtramos por nombre
			if($palabras!=null){			
				$fieldQuery1 = new \Elastica\Query\Match();
				$fieldQuery1->setFieldQuery('nombre', $palabras);
				$boolQuery2->addShould($fieldQuery1);
				
				$fieldQuery2 = new \Elastica\Query\Match();
				$fieldQuery2->setFieldQuery('telefono', $palabras);
				$boolQuery2->addShould($fieldQuery2);
				
				$fieldQuery3 = new \Elastica\Query\Match();
				$fieldQuery3->setFieldQuery('cif', $palabras);
				$boolQuery2->addShould($fieldQuery3);
				
				$fieldQuery4 = new \Elastica\Query\Match();
				$fieldQuery4->setFieldQuery('direccion', $palabras);
				$boolQuery2->addShould($fieldQuery4);
				
				$fieldQuery5 = new \Elastica\Query\Match();
				$fieldQuery5->setFieldQuery('city.name', $palabras);
				$boolQuery2->addShould($fieldQuery5);
			}else{
				$fieldQuery = new \Elastica\Query\MatchAll();
				$boolQuery2->addShould($fieldQuery);
			}
			
			$boolQuery->addMust($boolQuery2);
			
			$finalQuery = new \Elastica\Query($boolQuery);
			
			$finalQuery->setHighlight(array(
				'pre_tags' => array('<strong class="highlight_servicio">'),
				'post_tags' => array('</strong>'),
				'fields' => array(
					'nombre' => array(
						'fragment_size' => 200,
						'number_of_fragments' => 1,
					),
					'telefono' => array(
						'fragment_size' => 200,
						'number_of_fragments' => 1,
					),
					'cif' => array(
						'fragment_size' => 200,
						'number_of_fragments' => 1,
					),
					'direccion' => array(
						'fragment_size' => 200,
						'number_of_fragments' => 1,
					),
					'city.name' => array(
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
     		$id_comunidad=$request->request->get('id_comunidad');
   
   		if ($request->isXmlHttpRequest()) {
   			$ComprobacionesService=$this->get('comprobaciones_service');
      		$servicio=$ComprobacionesService->comprobar_servicio_cif($cif_servicio); 
      		$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);  
   			if($servicio!=null){
   						
   				$nombre=$servicio->getNombre();
   				$url=$this->generateUrl('ec_adminfincas_ver_servicio', array('id_comunidad'=>$comunidad->getId(), 'id_servicio'=>$servicio->getId()));
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
	  * @Route("/adminfincas/comunidad/{id_comunidad}/servicios/{id_servicio}", name="ec_adminfincas_ver_servicio")
	  * @Template("ECPrincipalBundle:Servicio:ver_servicio.html.twig")
	  */
    public function verAction($id_comunidad,$id_servicio, Request $request)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
    		$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
      	$servicio=$ComprobacionesService->comprobar_servicio($id_servicio); 
      	
      	$valoracion=$ComprobacionesService->comprobar_valoracion($this->getUser(),$servicio);
      	
      	if($valoracion!=null){
      		$form=$this->createForm(new ValoracionType(),$valoracion, array(
					'action' => $this->generateUrl('ec_adminfincas_ver_servicio',array('id_comunidad'=>$comunidad->getId(),'id_servicio'=>$servicio->getId())),      		
      		));
      	}else{
      		$valoracion = new Valoracion();
    			$form=$this->createForm(new ValoracionType(),$valoracion, array(
					'action' => $this->generateUrl('ec_adminfincas_ver_servicio',array('id_comunidad'=>$comunidad->getId(),'id_servicio'=>$servicio->getId())),      		    			
    			));
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
				return $this->redirect($this->generateUrl('ec_adminfincas_ver_servicio',array('id_comunidad'=>$comunidad->getId(),'id_servicio'=>$servicio->getId())));
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
	  * @Route("/adminfincas/comunidad/{id_comunidad}/servicios/añadir/{id_servicio}", name="ec_adminfincas_añadir_servicio")
	  * @Template("ECPrincipalBundle:Servicio:ver_servicio.html.twig")
	  */
    public function añadirAction($id_comunidad,$id_servicio)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
    		$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
      	$servicio=$ComprobacionesService->comprobar_servicio($id_servicio);
      	
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
			
        	return $this->redirect($this->generateUrl('ec_listado_servicios', array('id_comunidad'=>$comunidad->getId())));	
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/servicios/quitar/{id_servicio}", name="ec_adminfincas_quitar_servicio")
	  * @Template("ECPrincipalBundle:Servicio:ver_servicio.html.twig")
	  */
    public function quitarAction($id_comunidad,$id_servicio)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
    		$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
      	$servicio=$ComprobacionesService->comprobar_servicio($id_servicio);
      	
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
			
         return $this->redirect($this->generateUrl('ec_listado_servicios', array('id_comunidad'=>$comunidad->getId())));	
    }
    
    /**
     * @Pdf()
     * @Route("/comunidad/servicios/listado/pdf/{id_comunidad}", name="ec_listado_servicios_pdf")
     * @Template("ECPrincipalBundle:Servicio:listado_pdf.html.twig")
     */
    public function listado_pdfAction($id_comunidad)
    {
    	if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
    	 }else{
    		$comunidad=$this->getUser()->getBloque()->getComunidad();	
    	 }		
      	 
    	$servicios=$comunidad->getServicios();    
    	$format = $this->get('request')->get('_format');
    	        
    	$filename = "servicios_".$comunidad->getCodigo().".pdf";
    	$response= $this->render(sprintf('ECPrincipalBundle:Servicio:listado_servicios_pdf.%s.twig', $format), array(
        		'servicios' => $servicios, 'comunidad' => $comunidad
    		));
    		
    	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		return $response;
    }
    
    /**
	  * @Route("/comunidad/servicios/listado/csv/{id_comunidad}", name="ec_listado_servicios_csv")
	  * @Template("ECPrincipalBundle:Servicio:listado_csv.html.twig")
	  */
    public function listado_csvAction($id_comunidad) {
    		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    			$ComprobacionesService=$this->get('comprobaciones_service');
      		$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
    		}else{
    			$comunidad=$this->getUser()->getBloque()->getComunidad();	
    	 	}		
      	
    		$servicios=$comunidad->getServicios();   
    	
			$filename = "servicios_".$comunidad->getCodigo().".csv";
	
			$response = $this->render('ECPrincipalBundle:Servicio:listado_servicios_csv.html.twig', array('servicios' => $servicios));
			$response->headers->set('Content-Type', 'text/csv');

			$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	}

}