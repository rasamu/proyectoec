<?php
namespace EC\PrincipalBundle\Form\Type;
 
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
 
class PropiedadType extends AbstractType
{	
	protected $comunidad;
	
	public function __construct($comunidad){
		$this->comunidad=$comunidad;
	}
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    		$comunidad = $this->comunidad;
    		 
         $builder->add('piso','text',array('label'=>'Propiedad'));
         $builder->add('propietario', new PropietarioType());
         $builder->add('bloque','entity',array(
         'class'=>'ECPrincipalBundle:Bloque',
         'property'=>'direccion',
         'query_builder' => function(\Doctrine\ORM\EntityRepository $repository) use ($comunidad){
            return $repository->getBloquesQueryBuilder($comunidad);
           }
         ));			
    }
 
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'EC\PrincipalBundle\Entity\Propiedad',
             'csrf_protection' => false,
        ));
    }
 
    public function getName()
    {
        return 'propiedad';
    }
}

