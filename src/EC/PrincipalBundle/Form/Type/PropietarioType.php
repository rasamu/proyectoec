<?php
namespace EC\PrincipalBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use EC\PrincipalBundle\Entity\Usuario;
 
class PropietarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('nombre','text');
    		$builder->add('telefono','text', array('label' => 'Teléfono', 'required' => false));
    		$builder->add('email','email',array('required' => false));
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EC\PrincipalBundle\Entity\Usuario',
             'csrf_protection' => false,
        ));
    }
 
    public function getName()
    {
        return 'propietario';
    }
}

