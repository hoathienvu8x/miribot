<?php

namespace BrainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('BrainBundle:Default:index.html.twig');
    }
}
