<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use EC\PrincipalBundle\Entity\Documento;
use EC\PrincipalBundle\Entity\ConsultaDocumento;
use Ps\PdfBundle\Annotation\Pdf;

class DocumentoController extends Controller
{
	/**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/documento/upload", name="ec_adminfincas_comunidad_documento_upload")
	  * @Template("ECPrincipalBundle:Documento:upload.html.twig")
	  */
	public function uploadAction(Request $request, $id_comunidad)
	{
		$ComprobacionesService=$this->get('comprobaciones_service');
      $comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad); 
					
    	$documento = new Documento();
    	$form = $this->createFormBuilder($documento, array('csrf_protection' => false))
    	  ->add('tipo', 'entity', array(
            'class' => 'ECPrincipalBundle:Tipo',
            'property'=>'nombre',
            'label' => 'Tipo',
            'empty_value' => 'Seleccione un tipo',
         ))
        ->add('descripcion','text',array('label'=>'Descripción','max_length'=>50))
        ->add('file','file', array('label'=>'Fichero'))
        ->getForm();

    	$form->handleRequest($request);

    	if ($form->isValid()) {
    	   $tipo=$form->get('tipo')->getData();
		   $documento->setTipo($tipo);	  
		   $tipo->addDocumento($documento);
		   $documento->setComunidad($comunidad);
		   $comunidad->addDocumento($documento);
		  
		   $em = $this->getDoctrine()->getManager();
		   $em->persist($documento);
		   $em->persist($comunidad);
         $em->persist($tipo);
         $em->flush();

			$flash=$this->get('translator')->trans('Documento subido con éxito.');
			$this->get('session')->getFlashBag()->add('notice',$flash);
   		$this->get('session')->getFlashBag()->add('color','green');
   		return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_documento_upload', array('id_comunidad'=>$comunidad->getId())));
    	}
    	
    	return $this->render('ECPrincipalBundle:Documento:upload.html.twig',
        	       		array('form' => $form->createView(),'comunidad'=>$comunidad,
        	      		));
	}
	
	/**
	  * @Route("/documentos/listado/{id_comunidad}", name="ec_listado_documentos")
	  * @Template("ECPrincipalBundle:Documento:listado_documentos.html.twig")
	  */
	public function listadoAction($id_comunidad=null)
	{
    		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    			$ComprobacionesService=$this->get('comprobaciones_service');
        		$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
    		}else{
    			$comunidad=$this->getUser()->getBloque()->getComunidad();	
    		}
    		
			$repository = $this->getDoctrine()
    		->getRepository('ECPrincipalBundle:Tipo');   		
    		$query = $repository->createQueryBuilder('p')
    		->getQuery();
			$tipos = $query->getResult();
    		
			$documentos=$comunidad->getDocumentos();    		
    		
			return $this->render('ECPrincipalBundle:Documento:listado_documentos.html.twig',
					array('tipos'=>$tipos,'documentos'=>$documentos,'comunidad'=>$comunidad
					)); 				 	
	}
	
	/**
	  * @Route("/adminfincas/comunidad/{id_comunidad}/documento/eliminar/{id}", name="ec_adminfincas_comunidad_eliminar_documento")
	  */
	public function eliminarAction($id_comunidad, $id)
	{
			$ComprobacionesService=$this->get('comprobaciones_service');
        	$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
        	
    		$ComprobacionesService=$this->get('comprobaciones_service');
      	$documento=$ComprobacionesService->comprobar_documento($id); 
    		
    		$comunidad->removeDocumento($documento);
    		$em = $this->getDoctrine()->getManager();
    		$em->remove($documento);
    		$em->persist($comunidad);
			$em->flush();
    			
    		$flash=$this->get('translator')->trans('Documento eliminado con éxito.');
    		$this->get('session')->getFlashBag()->add('notice',$flash);
   		$this->get('session')->getFlashBag()->add('color','green');
   		return $this->redirect($this->generateUrl('ec_listado_documentos', array('id_comunidad'=>$comunidad->getId()))); 						 	
	}
	
	/**
	  * @Route("/documento/descargar/{id}", name="ec_documento_descargar")
	  */
	public function descargarAction($id)
	{
			$ComprobacionesService=$this->get('comprobaciones_service');
      	$documento=$ComprobacionesService->comprobar_documento($id);
    		
			$path = $documento->getWebPath();
         $content = file_get_contents($path);

         $response = new Response();
         
         switch($documento->getFormat()){
         	case 'pdf':
         		$response->headers->set('Content-Type', 'application/pdf');
         		break;
         	case 'odt':
         	   $response->headers->set('Content-Type', 'application/vnd.oasis.opendocument.text');
         		break;
         	case 'odp':
         		$response->headers->set('Content-Type', 'application/vnd.oasis.opendocument.presentation');
         		break;
         	case 'ods':
         		$response->headers->set('Content-Type', 'application/vnd.oasis.opendocument.spreadsheet');
         		break;
         	case 'doc':
         		$response->headers->set('Content-Type', 'application/msword');
         		break;
         	default:
         		$response->headers->set('Content-Type', 'text/plain');     			
         }

         $response->headers->set('Content-Disposition', 'attachment;filename="'.'documento'.'.'.$documento->getFormat());

         $response->setContent($content);
         
         //Registramos la consulta al documento
         if($this->get('security.context')->isGranted('ROLE_VECINO')){
				$consulta=$ComprobacionesService->comprobar_consulta_documento($this->getUser(), $documento->getId());
				$em = $this->getDoctrine()->getManager();
				if($consulta!=null){
					$consulta->setFecha();
				}else{
					$consulta=new ConsultaDocumento();
					$consulta->setPropietario($this->getUser());
					$consulta->setDocumento($documento);
					$this->getUser()->addConsultasDocumento($consulta);
					$documento->addConsultasDocumento($consulta);
					$em->persist($this->getUser());
					$em->persist($documento);	
				}  	
				$em->persist($consulta);
   			$em->flush();
   		}	
   		
         return $response;
	}
	
	/**
	  * @Pdf()
	  * @Route("/adminfincas/documento/consultas/{id}", name="ec_adminfincas_consultas_documento")
     * @Template("ECPrincipalBundle:Documento:consultas_documento_pdf.html.twig")	  
	  */
	public function consultasAction($id)
	{
			$ComprobacionesService=$this->get('comprobaciones_service');
      	$documento=$ComprobacionesService->comprobar_documento($id);
    		
			$comunidad=$documento->getComunidad();
			
			$format = $this->get('request')->get('_format');
    	        
    		$filename = "consultas_".$comunidad->getCodigo().".pdf";
    		$response= $this->render(sprintf('ECPrincipalBundle:Documento:consultas_documento_pdf.%s.twig', $format), array(
        		'comunidad' => $comunidad, 'bloques'=>$comunidad->getBloques(), 'documento' => $documento
    			));
    		
    		$response->headers->set('Content-Disposition', 'attachment; filename='.$filename);
			return $response;
	}
	
	/**
	  * @Route("/{id_comunidad}/documento/previsualizar/{id}", name="ec_documento_previsualizar")
	  * @Template("ECPrincipalBundle:Documento:previsualizar.html.twig")
	  */
	public function previsualizarAction($id, $id_comunidad=null)
	{
			$ComprobacionesService=$this->get('comprobaciones_service');
			
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
        		$comunidad=$ComprobacionesService->comprobar_comunidad($id_comunidad);
    		}else{
    			$comunidad=$this->getUser()->getBloque()->getComunidad();	
    		}
    		
      	$documento=$ComprobacionesService->comprobar_documento($id);
      	
      	//Registramos la consulta al documento
         if($this->get('security.context')->isGranted('ROLE_VECINO')){
				$consulta=$ComprobacionesService->comprobar_consulta_documento($this->getUser(), $documento->getId());
				$em = $this->getDoctrine()->getManager();
				if($consulta!=null){
					$consulta->setFecha();
				}else{
					$consulta=new ConsultaDocumento();
					$consulta->setPropietario($this->getUser());
					$consulta->setDocumento($documento);
					$this->getUser()->addConsultasDocumento($consulta);
					$documento->addConsultasDocumento($consulta);
					$em->persist($this->getUser());
					$em->persist($documento);	
				}  	
				$em->persist($consulta);
   			$em->flush();
   		}	
    		
			return $this->render('ECPrincipalBundle:Documento:previsualizar.html.twig',
					array('documento'=>$documento,'comunidad'=>$comunidad
					)); 			 	
	}
    
}
