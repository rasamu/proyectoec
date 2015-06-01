<?php
namespace EC\PrincipalBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use EC\PrincipalBundle\Form\EventListener\AddCityFieldSubscriber;
use EC\PrincipalBundle\Form\EventListener\AddProvinceFieldSubscriber;
use EC\PrincipalBundle\Form\EventListener\AddCountryFieldSubscriber;
 
class ServicioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('cif','text', array('label' => 'Cif/Nif','max_length' =>9,'required' => true));
         $builder->add('categoria', 'entity', array(
            'class' => 'ECPrincipalBundle:CategoriaServicios',
            'property'=>'nombre',
            'label' => 'Categoría',
            'empty_value' => 'Selecciona una categoría',
         ));
    		$builder->add('nombre','text',array('required' => true));
    		$builder->add('telefono','text', array('required' => true,'label' => 'Teléfono','max_length' =>9));
    		$builder->add('direccion','text', array('label' => 'Dirección'));
			$factory = $builder->getFormFactory();
         $citySubscriber = new AddCityFieldSubscriber($factory);
         $builder->addEventSubscriber($citySubscriber);
         $provinceSubscriber = new AddProvinceFieldSubscriber($factory);
         $builder->addEventSubscriber($provinceSubscriber);
         $countrySubscriber = new AddCountryFieldSubscriber($factory);
         $builder->addEventSubscriber($countrySubscriber);   			
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EC\PrincipalBundle\Entity\Servicio',
             'csrf_protection' => false,
        ));
    }
 
    public function getName()
    {
        return 'servicio';
    }
}