<?php
namespace EC\ComunidadBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use EC\PrincipalBundle\Form\EventListener\AddCityFieldSubscriber;
use EC\PrincipalBundle\Form\EventListener\AddProvinceFieldSubscriber;
use EC\PrincipalBundle\Form\EventListener\AddCountryFieldSubscriber;
 
class ComunidadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('cif','text',array('max_length' =>9));
    	 	$builder->add('direccion','text',array('label' => 'Direccion'));
    		$builder->add('n_plazas_garaje','integer',array('label' => 'N Plazas de garaje'));
    		$builder->add('n_locales_comerciales','integer',array('label' => 'N Locales Comerciales'));
    		$builder->add('n_piscinas','integer',array('label' => 'N Piscinas'));
    		$builder->add('n_pistas','integer',array('label' => 'N Pistas'));
    		$builder->add('gimnasio','choice',array('choices'=>array('1' => 'Si', '0' => 'No')));
    		$builder->add('ascensor','choice',array('choices'=>array('1' => 'Si', '0' => 'No')));
    		$builder->add('conserjeria','choice',array('choices'=>array('1' => 'Si', '0' => 'No')));
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
            'data_class' => 'EC\ComunidadBundle\Entity\Comunidad',
             'csrf_protection' => false,
        ));
    }
 
    public function getName()
    {
        return 'comunidad';
    }
}

