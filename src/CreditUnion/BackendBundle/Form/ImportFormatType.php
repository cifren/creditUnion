<?php

namespace CreditUnion\BackendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImportFormatType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('id', 'hidden')
                ->add('enabled')
                ->add('folder', 'text')
                ->add('dateFormat', 'text')
                ->add('type', 'choice', array(
                    'choices' => array('csv' => 'Csv', 'xls' => 'Excel'),)
                )
                ->add('titleDisplayed')
                ->add('delimiterCsv')
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => array('class' => 'table'),
            'data_class' => 'CreditUnion\BackendBundle\Entity\ImportFormat'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'creditunion_backendbundle_imporformat';
    }

}
