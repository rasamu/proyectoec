<?php

namespace EC\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ECBackendBundle:Default:index.html.twig');
    }
}
