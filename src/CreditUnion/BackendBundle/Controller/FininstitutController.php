<?php

namespace CreditUnion\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditUnion\FrontendBundle\Entity\Fininstitut;
use CreditUnion\BackendBundle\Form\FininstitutType;

/**
 * Fininstitut controller.
 *
 * @Route("/fininstitut")
 */
class FininstitutController extends Controller {

    /**
     * Lists all Fininstitut entities.
     *
     * @Route("/", name="cr_backend_fininstitut_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CreditUnionFrontendBundle:Fininstitut')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Fininstitut entity.
     *
     * @Route("/", name="cr_backend_fininstitut_create")
     * @Method("POST")
     * @Template("CreditUnionFrontendBundle:Fininstitut:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Fininstitut();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('cr_backend_fininstitut_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Fininstitut entity.
     *
     * @param Fininstitut $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Fininstitut $entity)
    {
        $form = $this->createForm(new FininstitutType(), $entity, array(
            'action' => $this->generateUrl('cr_backend_fininstitut_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
     * Displays a form to create a new Fininstitut entity.
     *
     * @Route("/new", name="cr_backend_fininstitut_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Fininstitut();
        $form = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Fininstitut entity.
     *
     * @Route("/{id}", name="cr_backend_fininstitut_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionFrontendBundle:Fininstitut')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Fininstitut entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Fininstitut entity.
     *
     * @Route("/{id}/edit", name="cr_backend_fininstitut_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionFrontendBundle:Fininstitut')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Fininstitut entity.');
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
     * Creates a form to edit a Fininstitut entity.
     *
     * @param Fininstitut $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Fininstitut $entity)
    {
        $form = $this->createForm(new FininstitutType(), $entity, array(
            'action' => $this->generateUrl('cr_backend_fininstitut_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Update', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
     * Edits an existing Fininstitut entity.
     *
     * @Route("/update/{id}", name="cr_backend_fininstitut_update")
     * @Method("POST")
     * @Template("CreditUnionBackendBundle:Fininstitut:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionFrontendBundle:Fininstitut')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Fininstitut entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('cr_backend_fininstitut_edit', array('id' => $id)));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Fininstitut entity.
     *
     * @Route("/delete/{id}", name="cr_backend_fininstitut_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $deleteForm = $this->createDeleteForm($id);
        $deleteForm->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('CreditUnionFrontendBundle:Fininstitut')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Fininstitut entity.');
        }

        if ($deleteForm->isValid()) {
            $em->remove($entity);
            $em->flush();
        } else {
            return $this->render('CreditUnionBackendBundle:Fininstitut:show.html.twig', array(
                        'entity' => $entity,
                        'delete_form' => $deleteForm->createView(),
            ));
        }

        return $this->redirect($this->generateUrl('cr_backend_fininstitut_index'));
    }

    /**
     * Creates a form to delete a Fininstitut entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('cr_backend_fininstitut_delete', array('id' => $id)))
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    /**
     * import data from file
     * 
     * @Route("/runcommand/{id}", name="cr_backend_fininstitut_runcommand")
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
        sleep(2);
        $fininstitut = $this->getDoctrine()->getRepository('CreditUnionFrontendBundle:Fininstitut')->find($id);
        return $this->redirect($this->generateUrl('cr_backend_importformat_displaylog', array('id' => $fininstitut->getImportFormat()->getId())));
    }

}
