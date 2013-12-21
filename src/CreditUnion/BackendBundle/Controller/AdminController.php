<?php

namespace CreditUnion\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AdminController extends Controller
{
    /**
     * @Route("/", name="cr_backend_admin_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
}
