<?php
namespace EC\PrincipalBundle\Form\Type;
 
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
         $builder->add('codigo','text',array('label' => 'Código Despacho'));
    		$builder->add('piscinas','choice',array('label' => 'Piscina','choices'=>array(0 => 'No', 1 => 'Si')));
    		$builder->add('pistas','choice',array('label' => 'Pistas Deportivas','choices'=>array(0 => 'No', 1 => 'Si')));
    		$builder->add('gimnasio','choice',array('choices'=>array(0 => 'No', 1 => 'Si')));
    		$builder->add('ascensor','choice',array('choices'=>array(1 => 'Si', 0 => 'No')));
    		$builder->add('conserjeria','choice',array('label'=>'Conserjería', 'choices'=>array(1 => 'Si', 0 => 'No')));
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
            'data_class' => 'EC\PrincipalBundle\Entity\Comunidad',
             'csrf_protection' => false,
        ));
    }
 
    public function getName()
    {
        return 'comunidad';
    }
}

