<?php

namespace CreditUnion\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FininstitutType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('class' => 'table'),
            'data_class' => 'CreditUnion\FrontendBundle\Entity\Fininstitut'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'creditunion_frontendbundle_fininstitut';
    }
}
