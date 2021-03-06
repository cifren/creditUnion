<?php

namespace CreditUnion\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query;
// these import the "@Route" and "@Template" annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ClientController extends Controller
{

    /**
     * @Route("/", name="cr_frontend_client_search")
     * @Template()
     */
    public function searchAction()
    {
        $em = $this->getDoctrine()->getManager();
        $fininstitutes = $em->getRepository('CreditUnionFrontendBundle:Fininstitut')->getFininstitutesWithClient();
        
        return array('fininstitutes' => $fininstitutes);
    }

    /**
     * JSON 
     * 
     * @Route("/advancedSearch", name="cr_frontend_client_list_adv")
     */
    public function listAdvAction()
    {
        $clientsQb = $this->getDoctrine()
                ->getRepository('CreditUnionFrontendBundle:Client')
                ->createQueryBuilder('c')
                ->select('c, b')
                ->innerJoin('c.fininstitut', 'b');
        if ($this->getRequest()->get('name') != '') {
            $clientsQb->andWhere('c.name LIKE :name')->setParameter('name', '%' . $this->getRequest()->get('name') . '%');
        }
        if ($this->getRequest()->get('birthDate') != '' && $this->validateDate($this->getRequest()->get('birthDate'))) {
            $clientsQb->andWhere('c.birthDate = :birthDate')->setParameter('birthDate', date_format(new \DateTime($this->getRequest()->get('birthDate')), 'Y/m/d'));
        }
        if ($this->getRequest()->get('panNumber') != '') {
            $clientsQb->andWhere('c.panNumber LIKE :panNumber')->setParameter('panNumber', '%' . $this->getRequest()->get('panNumber') . '%');
        }
        if ($this->getRequest()->get('accountNumber') != '') {
            $clientsQb->andWhere('c.accountNumber LIKE :accountNumber')->setParameter('accountNumber', '%' . $this->getRequest()->get('accountNumber') . '%');
        }
        if ($this->getRequest()->get('fininstitut') != '') {
            $clientsQb->andWhere('b.id = :fininstitut')->setParameter('fininstitut', $this->getRequest()->get('fininstitut'));
        }
        if ($this->getRequest()->get('branch') != '') {
            $clientsQb->andWhere('c.branch LIKE :branch')->setParameter('branch', "%{$this->getRequest()->get('branch')}%");
        }
        $clients = $clientsQb
                        ->setMaxResults(20)
                        ->getQuery()->getResult(Query::HYDRATE_ARRAY);

        $response = new Response(json_encode($clients));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * JSON 
     * 
     * @Route("/getClient/{clientId}", name="cr_frontend_client_getclient")
     */
    public function getClientAction($clientId)
    {
        $client = $this->getDoctrine()->getRepository('CreditUnionFrontendBundle:Client')->createQueryBuilder('c')
                ->select('c, b')
                ->innerJoin('c.fininstitut', 'b')
                ->where('c.id = :id')
                ->setParameter('id', $clientId)
                ->getQuery()
                ->getSingleResult(Query::HYDRATE_ARRAY);
        
        if($client['birthDate'] ){
            $client['birthDate'] = \date_format($client['birthDate'], 'Y-m-d');
        }
        $response = new Response(json_encode($client));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * Will get template for angular.js
     * @Route("/template/{templateName}", name="cr_frontend_client_template")
     * 
     */
    public function templateAction($templateName)
    {

        return $this->render('CreditUnionFrontendBundle:Client:' . $templateName);
    }

    function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

}
