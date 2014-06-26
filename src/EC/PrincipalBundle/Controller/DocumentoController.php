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

class DocumentoController extends Controller
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
	 
	 private function comprobar_documento($id){
	 		$em = $this->getDoctrine()->getManager();
			$query = $em->createQuery(
    				'SELECT d
       			FROM ECPrincipalBundle:Documento d
      			WHERE d.id = :id'
			)->setParameters(array('id' => $id));
			
			try {
    				$documento = $query->getSingleResult();
			} catch (\Doctrine\Orm\NoResultException $e) {
        			throw new AccessDeniedException();
			}	
			$aux=$documento->getComunidad();
			
    		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    			$comunidad=$this->comprobar_comunidad($aux->getCif());
    		}else{
    			$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();	
    			if($comunidad!=$aux){
    				throw new AccessDeniedException();
    			}
    		}
    		
    		return $documento;
	 }
	 
	/**
	  * @Route("/adminfincas/comunidad/{cif}/documento/upload", name="ec_adminfincas_comunidad_documento_upload")
	  * @Template("ECPrincipalBundle:Documento:upload.html.twig")
	  */
	public function uploadAction(Request $request, $cif)
	{
		$comunidad=$this->comprobar_comunidad($cif); 
					
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
   		return $this->redirect($this->generateUrl('ec_adminfincas_comunidad_documento_upload', array('cif'=>$comunidad->getCif())));
    	}
    	
    	return $this->render('ECPrincipalBundle:Documento:upload.html.twig',
        	       		array('form' => $form->createView(),'comunidad'=>$comunidad,
        	      		));
	}
	
	/**
	  * @Route("/documentos/listado/{cif}", name="ec_listado_documentos")
	  * @Template("ECPrincipalBundle:Documento:listado_documentos.html.twig")
	  */
	public function listadoAction($cif=null)
	{
    		if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    			$comunidad=$this->comprobar_comunidad($cif);
    		}else{
    			$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();	
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
	  * @Route("/adminfincas/comunidad/{cif}/documento/eliminar/{id}", name="ec_adminfincas_comunidad_eliminar_documento")
	  */
	public function eliminarAction($cif, $id)
	{
    		$comunidad=$this->comprobar_comunidad($cif);		
    		$documento=$this->comprobar_documento($id);
    		
    		$comunidad->removeDocumento($documento);
    		$em = $this->getDoctrine()->getManager();
    		$em->remove($documento);
    		$em->persist($comunidad);
			$em->flush();
    			
    		$flash=$this->get('translator')->trans('Documento eliminado con éxito.');
    		$this->get('session')->getFlashBag()->add('notice',$flash);
   		$this->get('session')->getFlashBag()->add('color','green');
   		return $this->redirect($this->generateUrl('ec_listado_documentos', array('cif'=>$comunidad->getCif()))); 						 	
	}
	
	/**
	  * @Route("/documento/descargar/{id}", name="ec_documento_descargar")
	  */
	public function descargarAction($id)
	{
			$documento=$this->comprobar_documento($id);
    		
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
         return $response;
	}
	
	/**
	  * @Route("/{cif}/documento/previsualizar/{id}", name="ec_documento_previsualizar")
	  * @Template("ECPrincipalBundle:Documento:previsualizar.html.twig")
	  */
	public function previsualizarAction($id, $cif=null)
	{
			if($this->get('security.context')->isGranted('ROLE_ADMINFINCAS')){
    			$comunidad=$this->comprobar_comunidad($cif);
    		}else{
    			$comunidad=$this->getUser()->getPropiedad()->getBloque()->getComunidad();	
    		}
    		
    		$documento=$this->comprobar_documento($id);
    		
			return $this->render('ECPrincipalBundle:Documento:previsualizar.html.twig',
					array('documento'=>$documento,'comunidad'=>$comunidad
					)); 
			 	
	}
    
}
