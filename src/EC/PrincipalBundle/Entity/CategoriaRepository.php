<?php
namespace EC\PrincipalBundle\Entity;
 
use Doctrine\ORM\EntityRepository;
 
class CategoriaRepository extends EntityRepository
{
    public function findById($id)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM ECPrincipalBundle:Categoria c WHERE c.id=$id"
            )
            ->getResult();
    }
}
?>