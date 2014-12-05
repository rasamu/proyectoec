<?php

namespace EC\PrincipalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;

class AnuncioController extends Controller
{
	/**
	  * @Route("/anuncios/listado/", name="ec_listado_anuncios")
	  * @Template("ECPrincipalBundle:Anuncio:listado_anuncios.html.twig")
	  */
	public function listadoAction($cif=null)
	{ 		
    		
		return $this->render('ECPrincipalBundle:Anuncio:listado_anuncios.html.twig'); 				 	
	}

}