<?php
namespace EC\PrincipalBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
 
class IncidenciaType extends AbstractType
{	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {	 
         $builder->add('categoria', 'entity', array(
            'class' => 'ECPrincipalBundle:Categoria',
            'property'=>'nombre',
            'label' => 'Categoría',
            'empty_value' => 'Selecciona una categoría',
         ));	
         $builder->add('asunto', 'text',array('max_length'=>50));		
         $builder->add('descripcion', 'textarea',array('label'=>'Descripción','max_length'=>500));		
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EC\PrincipalBundle\Entity\Incidencia',
        ));
    }
 
    public function getName()
    {
        return 'incidencia';
    }
}

