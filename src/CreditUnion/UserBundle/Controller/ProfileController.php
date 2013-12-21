<?php

namespace CreditUnion\UserBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Controller\ProfileController as baseProfileController;
use CreditUnion\UserBundle\Form\Type\ProfileFormType;
use CreditUnion\UserBundle\Form\Handler\ProfileFormHandler;

/**
 * CreditUnion\UserBundle\Controller\ProfileController
 */
class ProfileController extends baseProfileController
{

    /**
     * Edit the user
     */
    public function editAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $form = $this->container->get('form.factory')->create(new ProfileFormType($this->container->getParameter('fos_user.model.user.class')));
        $formHandler = new ProfileFormHandler($form, $this->container->get('request'), $this->container->get('fos_user.user_manager'));

        $process = $formHandler->process($user);
        if ($process) {
            $this->setFlash('fos_user_success', 'Updated');
        }

        return $this->container->get('templating')->renderResponse(
                        'FOSUserBundle:Profile:edit.html.' . $this->container->getParameter('fos_user.template.engine'), array('form' => $form->createView())
        );
    }

}
