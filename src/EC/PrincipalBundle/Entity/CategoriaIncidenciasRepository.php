<?php
namespace EC\PrincipalBundle\Entity;
 
use Doctrine\ORM\EntityRepository;
 
class CategoriaIncidenciasRepository extends EntityRepository
{
    public function findById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM ECPrincipalBundle:CategoriaIncidencias c WHERE c.id=$id"
            )
            ->getResult();
    }
}
?>