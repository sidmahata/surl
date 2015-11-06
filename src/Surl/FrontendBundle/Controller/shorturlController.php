<?php

namespace Surl\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ShorturlController extends Controller
{
    public function indexAction()
    {
        return $this->render('SurlFrontendBundle:Shorturl:index.html.twig', array(

        ));
    }
}
