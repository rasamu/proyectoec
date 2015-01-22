<?php
 
namespace EC\PrincipalBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
 
class ImagenType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
 		$builder->add('file','file',array('label'=>'Imagen','required'=>false,'error_bubbling' => false,));
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'EC\PrincipalBundle\Entity\Imagen',
			'cascade_validation' => true,
			'csrf_protection' => false,
         'allow_add'    => true,
         'error_bubbling' => false,
		));
	}
 
	public function getName()
	{
		return 'imagen';
	}
} 