<?php
namespace EC\PrincipalBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
 
class AnuncioType extends AbstractType
{	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {	 
    	   $builder->add('categoria', 'entity', array(
            'class' => 'ECPrincipalBundle:CategoriaAnuncios',
            'property'=>'nombre',
            'label' => 'Categoría',
            'empty_value' => 'Selecciona una categoría',
         ));	
         $builder->add('titulo', 'text', array('label'=>'Título','max_length'=>50));
         $builder->add('descripcion', 'textarea', array('label'=>'Descripción','max_length'=>255));
         $builder->add('precio', 'number', array('label'=>'Precio (€uros)', 'max_length'=>11));
         $builder->add('file_1','file', array('label'=>'Imagen 1'));
         $builder->add('file_2','file', array('label'=>'Imagen 2'));
        	$builder->add('file_3','file', array('label'=>'Imagen 3'));
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EC\PrincipalBundle\Entity\Anuncio',
            'csrf_protection' => false,
        ));
    }
 
    public function getName()
    {
        return 'anuncio';
    }
}
