<?php

namespace CreditUnion\BackendBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use CreditUnion\UserBundle\Entity\MyUser;
use CreditUnion\BackendBundle\Form\MyUserType;
use CreditUnion\BackendBundle\Form\NewMyUserType;
use CreditUnion\BackendBundle\Form\ResetpwMyUserType;
use CreditUnion\UserBundle\Form\Handler\ProfileFormHandler;

/**
 * MyUser controller.
 *
 * @Route("/user")
 */
class MyUserController extends Controller
{

    /**
     * Lists all MyUser entities.
     *
     * @Route("/", name="cr_backend_user_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('CreditUnionUserBundle:MyUser')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new MyUser entity.
     *
     * @Route("/", name="cr_backend_user_create")
     * @Method("POST")
     * @Template("CreditUnionBackendBundle:MyUser:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new MyUser();
        $entity->setEnabled(true);
        $form = $this->createForm(new NewMyUserType($this->container->getParameter('security.role_hierarchy.roles')), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');

            $userManager->updateUser($entity);

            $this->setFlash('fos_user_success', 'Created');
            return $this->redirect($this->generateUrl('cr_backend_user_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new MyUser entity.
     *
     * @Route("/new", name="cr_backend_user_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new MyUser();
        $entity->setPlainPassword('panini36');
        $entity->setEnabled(true);
        $form = $this->container->get('form.factory')->create(new NewMyUserType($this->container->getParameter('security.role_hierarchy.roles')), $entity);

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a MyUser entity.
     *
     * @Route("/{id}", name="cr_backend_user_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionUserBundle:MyUser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MyUser entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing MyUser entity.
     *
     * @Route("/{id}/edit", name="cr_backend_user_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionUserBundle:MyUser')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MyUser entity.');
        }

        $editForm = $this->container->get('form.factory')->create(new MyUserType($this->container->getParameter('security.role_hierarchy.roles')), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing MyUser entity.
     *
     * @Route("/passwd/{id}", name="cr_backend_user_resetpw")
     * @Template()
     */
    public function resetpwAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionUserBundle:MyUser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MyUser entity.');
        }

        $editForm = $this->container->get('form.factory')->create(new ResetpwMyUserType($this->container->getParameter('fos_user.model.user.class')), $entity);

        if ($request->getMethod() == 'POST') {
            $editForm->bind($request);

            if ($editForm->isValid()) {
                $this->container->get('fos_user.user_manager')->updateUser($entity);
                $this->setFlash('fos_user_success', 'Updated');
            }
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Edits an existing MyUser entity.
     *
     * @Route("/{id}/{from}", name="cr_backend_user_update")
     * @Method("POST")
     */
    public function updateAction(Request $request, $id, $from)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('CreditUnionUserBundle:MyUser')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find MyUser entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        $editForm = $this->container->get('form.factory')->create(new MyUserType($this->container->getParameter('security.role_hierarchy.roles')));
        $formHandler = new ProfileFormHandler($editForm, $request, $this->container->get('fos_user.user_manager'));

        $process = $formHandler->process($entity);
        if ($process) {
            $this->setFlash('fos_user_success', 'Updated');
            //return $this->redirect($this->generateUrl('cr_backend_user_edit', array('id' => $id)));
        }

        return $this->render('CreditUnionBackendBundle:MyUser:' . $from . '.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a MyUser entity.
     *
     * @Route("/{id}", name="cr_backend_user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('CreditUnionUserBundle:MyUser')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find MyUser entity.');
            }

            $this->setFlash('fos_user_success', 'Updated');
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('user'));
    }

    /**
     * Creates a form to delete a MyUser entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('cr_backend_user_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete', 'attr' => array('class' => 'btn btn-danger')))
                        ->getForm()
        ;
    }

    /**
     * @param string $action
     * @param string $value
     */
    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->set($action, $value);
    }

}
