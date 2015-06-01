<?php
namespace EC\PrincipalBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
 
class ValoracionType extends AbstractType
{	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {	 
         $builder->add('mensaje', 'textarea',array('label'=>'Mensaje','max_length'=>500));
         $builder->add('puntuacion', 'rating');
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EC\PrincipalBundle\Entity\Valoracion',
            'csrf_protection' => false,
        ));
    }
 
    public function getName()
    {
        return 'valoracion';
    }
}