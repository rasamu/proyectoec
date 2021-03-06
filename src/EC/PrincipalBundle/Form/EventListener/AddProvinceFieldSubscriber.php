<?php

namespace EC\PrincipalBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\ORM\EntityRepository;
use EC\PrincipalBundle\Entity\Country;

class AddProvinceFieldSubscriber implements EventSubscriberInterface
{
    private $factory;

    public function __construct(FormFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_BIND => 'preBind'
        );
    }

    private function addProvinceForm($form, $province, $country)
    {
        $form->add('province','entity', array(
            'class' => 'ECPrincipalBundle:Province',
            'label' => 'Provincia',
            'empty_value' => 'Provincia',
            'mapped' => false,
            'data' => $province,
            'attr' => array(
                'class' => 'province_selector',
            ),
            'query_builder' => function (EntityRepository $repository) use ($country) {
                $qb = $repository->createQueryBuilder('province')
                    ->innerJoin('province.country', 'country');
                if ($country instanceof Country) {
                    $qb->where('province.country = :country')
                    ->setParameter('country', $country);
                } elseif (is_numeric($country)) {
                    $qb->where('country.id = :country')
                    ->setParameter('country', $country);
                } else {
                    $qb->where('country.name = :country')
                    ->setParameter('country', null);
                }

                return $qb;
            }
        ));
    }

    public function preSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $accessor = PropertyAccess::getPropertyAccessor();
        $city = $accessor->getValue($data, 'city');
        $province = ($city) ? $city->getProvince() : null ;
        $country = ($province) ? $province->getCountry() : null ;
        $this->addProvinceForm($form, $province, $country);
    }

    public function preBind(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        $province = array_key_exists('province', $data) ? $data['province'] : null;
        $country = array_key_exists('country', $data) ? $data['country'] : null;
        $this->addProvinceForm($form, $province, $country);
    }
}