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
    		$builder->add('telefono','integer', array('label' => 'Teléfono','max_length' =>9, 'required' => false));
    		$builder->add('email','text',array('required' => false));
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

