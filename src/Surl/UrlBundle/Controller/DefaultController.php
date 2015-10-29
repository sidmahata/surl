<?php

namespace Surl\UrlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SurlUrlBundle:Default:index.html.twig', array('name' => $name));
    }
}
