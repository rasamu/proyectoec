<?php
namespace EC\PrincipalBundle\Entity;
 
use Doctrine\ORM\EntityRepository;
 
class EstadoRepository extends EntityRepository
{
    public function findById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM ECPrincipalBundle:Estado c WHERE c.id=$id"
            )
            ->getResult();
    }
}
?>