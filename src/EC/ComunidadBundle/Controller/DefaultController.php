<?php

namespace EC\ComunidadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ECComunidadBundle:Default:index.html.twig', array('name' => $name));
    }
}
