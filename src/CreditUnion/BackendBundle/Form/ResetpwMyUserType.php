<?php

namespace CreditUnion\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResetpwMyUserType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plain_password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Password does\'nt match',
                'first_options' => array('label' => 'New Password'),
                'second_options' => array('label' => 'Confirm New Password')
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CreditUnion\UserBundle\Entity\MyUser'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'creditunion_Backendbundle_myuser';
    }
}
