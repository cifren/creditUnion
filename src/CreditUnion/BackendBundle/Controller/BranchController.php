<?php

namespace CreditUnion\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditUnion\FrontendBundle\Entity\Branch;
use CreditUnion\BackendBundle\Form\BranchType;

/**
 * Branch controller.
 *
 * @Route("/branch")
 */
class BranchController extends Controller {

    /**
     * Lists all Branch entities.
     *
     * @Route("/", name="cr_backend_branch_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CreditUnionFrontendBundle:Branch')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Branch entity.
     *
     * @Route("/", name="cr_backend_branch_create")
     * @Method("POST")
     * @Template("CreditUnionFrontendBundle:Branch:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Branch();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('cr_backend_branch_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Branch entity.
     *
     * @param Branch $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Branch $entity)
    {
        $form = $this->createForm(new BranchType(), $entity, array(
            'action' => $this->generateUrl('cr_backend_branch_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
     * Displays a form to create a new Branch entity.
     *
     * @Route("/new", name="cr_backend_branch_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Branch();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Branch entity.
     *
     * @Route("/{id}", name="cr_backend_branch_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionFrontendBundle:Branch')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Branch entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Branch entity.
     *
     * @Route("/{id}/edit", name="cr_backend_branch_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionFrontendBundle:Branch')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Branch entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Creates a form to edit a Branch entity.
     *
     * @param Branch $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Branch $entity)
    {
        $form = $this->createForm(new BranchType(), $entity, array(
            'action' => $this->generateUrl('cr_backend_branch_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
     * Edits an existing Branch entity.
     *
     * @Route("/{id}", name="cr_backend_branch_update")
     * @Method("PUT")
     * @Template("CreditUnionBackendBundle:Branch:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionFrontendBundle:Branch')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Branch entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('cr_backend_branch_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Branch entity.
     *
     * @Route("/{id}", name="cr_backend_branch_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $deleteForm = $this->createDeleteForm($id);
        $deleteForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CreditUnionFrontendBundle:Branch')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Branch entity.');
        }

        if ($deleteForm->isValid()) {
            $em->remove($entity);
            $em->flush();
        } else {
            return $this->render('CreditUnionBackendBundle:Branch:show.html.twig', array(
                        'entity' => $entity,
                        'delete_form' => $deleteForm->createView(),
            ));
        }

        return $this->redirect($this->generateUrl('cr_backend_branch_index'));
    }

    /**
     * Creates a form to delete a Branch entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('cr_backend_branch_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    /**
     * import data from file
     * 
     * @Route("/runcommand/{id}", name="cr_backend_branch_runcommand")
     * @Method("GET")
     */
    public function runImport($id)
    {
        $path = $this->get('kernel')->getRootDir();
        $cmd = "php {$path}/console import:clientFromFtp $id";
        $outputfile = "/tmp/plop";
        echo "$cmd";

        if (substr(php_uname(), 0, 7) == "Windows") {
            pclose(popen("start /B " . $cmd. ' ^>"'.$outputfile. '"', "r"));
        } else {
            exec($cmd . " > {$outputfile}2 &");
        }
        
        //die();
        return $this->redirect($this->generateUrl('cr_backend_importformat_displaylog', array('id' => $id)));
    }

}
