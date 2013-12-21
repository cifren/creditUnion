<?php

namespace CreditUnion\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class WelcomeController extends Controller
{
    public function indexAction()
    {
        return new RedirectResponse($this->generateUrl('cr_frontend_client_search'));
    }
}
