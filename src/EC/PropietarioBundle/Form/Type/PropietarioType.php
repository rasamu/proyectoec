<?php
namespace EC\PropietarioBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
 
class PropietarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('nombre','text');
    		$builder->add('telefono','integer', array('label' => 'Teléfono', 'required' => false, 'attr' => array('min' => 9, 'max'=> 9)));
    		$builder->add('email','email',array('required' => false));
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EC\PropietarioBundle\Entity\Propietario',
             'csrf_protection' => false,
        ));
    }
 
    public function getName()
    {
        return 'propietario';
    }
}

