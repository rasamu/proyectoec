<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use EC\PrincipalBundle\Entity\Propietario;
use EC\PrincipalBundle\Entity\Incidencia;
use EC\PrincipalBundle\Entity\ConsultaIncidencia;
use EC\PrincipalBundle\Entity\Actuacion;
use EC\PrincipalBundle\Entity\Estado;
use EC\PrincipalBundle\Entity\CategoriaIncidencias;
use EC\PrincipalBundle\Entity\Privacidad;
use EC\PrincipalBundle\Form\Type\IncidenciaType;
use EC\PrincipalBundle\Form\Type\ActuacionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Ps\PdfBundle\Annotation\Pdf;

class IncidenciaController extends Controller
{  	     
    /**
	  * @Route("/propietario/incidencia/nueva", name="ec_propietario_nueva_incidencia")
	  * @Template("ECPrincipalBundle:Incidencia:nueva_incidencia.html.twig")
	  */
    public function nueva_incidenciaAction(Request $request)
    {
    		$incidencia = new Incidencia();
    		$form=$this->createForm(new IncidenciaType(),$incidencia,array('action' => $this->generateUrl('ec_propietario_nueva_incidencia')));
    				
    		$form->handleRequest($request);
    			
    		if ($form->isValid()) {					
    				$categoria=$form->get('categoria')->getData();
					$estado=$this->getDoctrine()->getRepository('ECPrincipalBundle:Estado')->findById('1');
					$privacidad=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('1');
					
					$incidencia->setCategoria($categoria);
					$incidencia->setEstado($estado[0]);
					$incidencia->setPrivacidad($privacidad[0]);
					$incidencia->setPropietario($this->getUser());
					$this->getUser()->addIncidencia($incidencia);
					$categoria->addIncidencia($incidencia);
					$estado[0]->addIncidencia($incidencia);
					$privacidad[0]->addIncidencia($incidencia);
					
    				$em = $this->getDoctrine()->getManager();
					$em->persist($incidencia);
					$em->persist($this->getUser());
   				$em->persist($categoria);
   				$em->persist($estado[0]);
   				$em->persist($privacidad[0]);
   				$em->flush();
   				
    				$em = $this->getDoctrine()->getManager();
					$consulta=new ConsultaIncidencia();
					$consulta->setUsuario($this->getUser());
					$consulta->setIncidencia($incidencia);
					$this->getUser()->addConsultasIncidencia($consulta);
					$incidencia->addConsultasIncidencia($consulta);

					$em->persist($this->getUser());
					$em->persist($incidencia);	
					$em->persist($consulta);
   				$em->flush();
   				
   				$comunidad=$this->getUser()->getBloque()->getComunidad();
   				$administrador=$comunidad->getAdministrador();
   				
   				/*Correo al administrador*/
   				$message = \Swift_Message::newInstance()
        			->setSubject('Incidencia Comunidad: '.$comunidad->getCodigo())
        			->setFrom('info.entrecomunidades@gmail.com')
        			->setTo($administrador->getEmail())
        			->setContentType('text/html')
        			->setBody($this->renderView('ECPrincipalBundle:Incidencia:email_nueva_incidencia.txt.twig', array('comunidad'=>$comunidad,'categoria'=>$categoria->getNombre(),'incidencia'=>$incidencia)));
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
	  * @Template("ECPrincipalBundle:Incidencia:listado_incidencias.html.twig")
	  */
    public function listado_incidenciasAction($cif=null)
    {
    		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS') or $this->get('security.context')->isGranted('ROLE_PRESIDENTE') or $this->get('security.context')->isGranted('ROLE_VICEPRESIDENTE')){
    			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    				$ComprobacionesService=$this->get('comprobaciones_service');
      			$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
    			}else{
    				$comunidad=$this->getUser()->getBloque()->getComunidad();	
    			}
    			
    			/*Buscamos las incidencias de la comunidad*/
    			$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT i
					FROM ECPrincipalBundle:Incidencia i
					WHERE i.propietario IN
					(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
					(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad))'
				)->setParameters(array('comunidad'=>$comunidad,));
				
				$incidencias = $query->getResult();		
    		}else{
    			$bloque=$this->getUser()->getBloque();
    			$comunidad=$bloque->getComunidad();
    			$privacidad_bloque_publica=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('2');
        		$privacidad_comunidad_publica=$this->getDoctrine()->getRepository('ECPrincipalBundle:Privacidad')->findById('3');
        				
    			/*Buscamos las incidencias del propietario privadas y de la comunidad o bloque publicas*/
    			$em = $this->getDoctrine()->getManager();
				$query = $em->createQuery(
    				'SELECT i
					FROM ECPrincipalBundle:Incidencia i
					WHERE (i.privacidad= :privacidad_comunidad_publica and i.propietario IN
						(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
						(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad)))
					or (i.privacidad= :privacidad_bloque_publica and i.propietario IN
						(SELECT s FROM ECPrincipalBundle:Propietario s WHERE s.bloque= :bloque))
					or (i.propietario= :propietario)'
				)->setParameters(array('comunidad'=>$comunidad,'bloque'=>$bloque,'propietario'=>$this->getUser(),'privacidad_comunidad_publica'=>$privacidad_comunidad_publica,'privacidad_bloque_publica'=>$privacidad_bloque_publica,));
				
				$incidencias = $query->getResult();	
				
    		}
    		
			return $this->render('ECPrincipalBundle:Incidencia:listado_incidencias.html.twig',
					array('incidencias'=>$incidencias,'comunidad'=>$comunidad,
					));
    }
    
    /**
     * @Pdf()
     * @Route("/adminfincas/comunidad/{cif}/listado/incidencias/pdf", name="ec_adminfincas_comunidad_listado_incidencias_pdf")
     */
    public function listado_incidencias_pdfAction($cif)
    {
    	$ComprobacionesService=$this->get('comprobaciones_service');
      $comunidad=$ComprobacionesService->comprobar_comunidad($cif);
    	
    	/*Buscamos las incidencias de la comunidad*/
    	$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
    		'SELECT i
			FROM ECPrincipalBundle:Incidencia i
			WHERE i.propietario IN
			(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
			(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad))'
		)->setParameters(array('comunidad'=>$comunidad,));			
		$incidencias = $query->getResult();
    	    
    	$format = $this->get('request')->get('_format');  	
    	$filename = "incidencias_".$comunidad->getCodigo().".pdf";
    	$response=$this->render(sprintf('ECPrincipalBundle:Incidencia:listado_incidencias_pdf.%s.twig', $format), array(
        		'incidencias' => $incidencias, 'comunidad'=>$comunidad
    		));
    	$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
		return $response;
    }
    
    /**
	  * @Route("/adminfincas/comunidad/{cif}/listado/incidencias/csv", name="ec_adminfincas_comunidad_listado_incidencias_csv")
	  * @Template("ECPrincipalBundle:Incidencia:listado_incidencias_csv.html.twig")
	  */
    public function listado_incidencias_csvAction($cif)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$comunidad=$ComprobacionesService->comprobar_comunidad($cif);
    		
    		/*Buscamos las incidencias de la comunidad*/
    		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    			'SELECT i
				FROM ECPrincipalBundle:Incidencia i
				WHERE i.propietario IN
				(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
				(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad = :comunidad))'
			)->setParameters(array('comunidad'=>$comunidad,));			
			$incidencias = $query->getResult();   
    		 
			$filename = "incidencias_".$comunidad->getCodigo().".csv";	
			$response = $this->render('ECPrincipalBundle:Incidencia:listado_incidencias_csv.html.twig', array('incidencias' => $incidencias));
			$response->headers->set('Content-Type', 'text/csv');

			$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	 }
    
    /**
	  * @Route("/incidencia/{id}", name="ec_incidencia")
	  * @Template("ECPrincipalBundle:Incidencia:ver_incidencia.html.twig")
	  */
    public function ver_incidenciaAction($id, Request $request)
    {
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$incidencia=$ComprobacionesService->comprobar_incidencia($id);
      	
    		$estado=$incidencia->getEstado();//lo almacenamos pq se cambia con el form
    		$privacidad=$incidencia->getPrivacidad();//lo almacenamos pq se cambia con el form
		   $comunidad=$incidencia->getPropietario()->getBloque()->getComunidad();
			$administrador=$comunidad->getAdministrador();
			$categoria=$incidencia->getCategoria();
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
   			
   				if(!$this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
   					/*Correo*/
   					$message = \Swift_Message::newInstance()
        					->setSubject('Nueva Actuación Comunidad: '.$comunidad->getCodigo())
        					->setFrom('info.proyectoec@gmail.com')
        					->setTo($administrador->getEmail())
        					->setContentType('text/html')
        					->setBody($this->renderView('ECPrincipalBundle:Incidencia:email_nueva_actuacion.txt.twig', array('categoria'=>$categoria->getNombre(),'asunto'=>$incidencia->getAsunto(),'comunidad'=>$comunidad,'actuacion'=>$actuacion)));
    					$this->get('mailer')->send($message); 
    				}		
    			
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
    		
			//Registramos la consulta a la incidencia
			$consulta=$ComprobacionesService->comprobar_consulta_incidencia($this->getUser(), $incidencia->getId());
			$em = $this->getDoctrine()->getManager();
			if($consulta!=null){
				$consulta->setFecha();
			}else{
				$consulta=new ConsultaIncidencia();
				$consulta->setUsuario($this->getUser());
				$consulta->setIncidencia($incidencia);
				$this->getUser()->addConsultasIncidencia($consulta);
				$incidencia->addConsultasIncidencia($consulta);

				$em->persist($this->getUser());
				$em->persist($incidencia);	
			}  	
			$em->persist($consulta);
   		$em->flush();		
    		
			return $this->render('ECPrincipalBundle:Incidencia:ver_incidencia.html.twig',
					array('incidencia'=>$incidencia, 'actuaciones'=>$actuaciones, 'form' => $form->createView(), 'form_estado' => $form_estado->createView(), 'form_privacidad' => $form_privacidad->createView(),'comunidad'=>$comunidad
					));
    }
    
    /**
	  * @Route("/adminfincas/estadisticas/incidencias/categorias", name="ec_adminfincas_estadisticas_incidencias_categorias")
	  * @Template("ECPrincipalBundle:Incidencia:estadisticas_incidencias_categoria.html.twig")
	  */
    public function estadisticasIncidenciasCategoriasAction()
    {   			
			/*Contamos las incidencias de todas las comunidades que administra el administrador por categorias*/
    		$em = $this->getDoctrine()->getManager();	
			$query = $em->createQuery(
    			'SELECT cat.nombre,(SELECT COUNT(i) FROM ECPrincipalBundle:Incidencia i
    			WHERE i.categoria=cat and i.propietario IN
				(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
				(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad IN
				(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.administrador= :admin)))) as total
				FROM ECPrincipalBundle:CategoriaIncidencias cat'
			)->setParameters(array('admin'=>$this->getUser()));			
			$categorias = $query->getResult();		  				
    		
    		return $this->render('ECPrincipalBundle:Incidencia:estadisticas_incidencias_categorias.html.twig',
					array('categorias'=>$categorias,
					));
    }
    
    /**
	  * @Route("/adminfincas/estadisticas/incidencias/fecha", name="ec_adminfincas_estadisticas_incidencias_fecha")
	  * @Template("ECPrincipalBundle:Incidencia:estadisticas_incidencias_fecha.html.twig")
	  */
    public function estadisticasIncidenciasFechaAction()
    {   			
			/*Contamos las incidencias de todas las comunidades que administra el administrador agrupadas por meses*/
    		$em = $this->getDoctrine()->getManager();	
			$query = $em->createQuery(
    			'SELECT MONTH(i.fecha) as mes, COUNT(i) as total 
    			FROM ECPrincipalBundle:Incidencia i
    			WHERE i.propietario IN
				(SELECT u FROM ECPrincipalBundle:Propietario u WHERE u.bloque IN
				(SELECT b FROM ECPrincipalBundle:Bloque b WHERE b.comunidad IN
				(SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.administrador= :admin))) group by mes'
			)->setParameters(array('admin'=>$this->getUser()));			
			$totales = $query->getResult();
			
			$meses=array('1'=>'0','2'=>'0','3'=>'0','4'=>'0','5'=>'0','6'=>'0','7'=>'0','8'=>'0','9'=>'0','10'=>'0','11'=>'0','12'=>'0');		    
    		
    		foreach($totales as $total){
    			$meses[$total['mes']]=$total['total'];	
    		}
    		
    		return $this->render('ECPrincipalBundle:Incidencia:estadisticas_incidencias_fecha.html.twig',
    				array('meses'=>$meses,
    				));	
    }
}