<?php

namespace CreditUnion\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MyUserType extends AbstractType
{

    public function __construct(array $securityContext)
    {
        $this->securityContext = array_keys($securityContext);
        $this->securityContext = array_combine($this->securityContext, array_values($this->securityContext));
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('username')
                ->add('email')
                ->add('enabled', 'checkbox', array(
                    'required' => false
                ))
                ->add('plain_password', 'repeated', array(
                    'type' => 'password',
                    'invalid_message' => 'Password does\'nt match',
                    'first_options' => array('label' => 'New Password'),
                    'second_options' => array('label' => 'Confirm New Password')
                ))
                ->add('locked', 'checkbox', array(
                    'required' => false
                ))
                ->add('group', 'entity', array(
                        'class' => 'CreditUnion\UserBundle\Entity\MyGroup',
                        'property' => 'name',
                ))
        ;
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
