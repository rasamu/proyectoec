<?php
namespace EC\PrincipalBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
 
class ActuacionType extends AbstractType
{	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {	 
         $builder->add('mensaje', 'textarea',array('label'=>'Mensaje','max_length'=>500));		
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EC\PrincipalBundle\Entity\Actuacion',
            'csrf_protection' => false,
        ));
    }
 
    public function getName()
    {
        return 'actuacion';
    }
}