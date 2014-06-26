<?php
namespace EC\PrincipalBundle\Entity;
 
use Doctrine\ORM\EntityRepository;
 
class PrivacidadRepository extends EntityRepository
{
    public function findById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT p FROM ECPrincipalBundle:Privacidad p WHERE p.id=$id"
            )
            ->getResult();
    }
}
?>