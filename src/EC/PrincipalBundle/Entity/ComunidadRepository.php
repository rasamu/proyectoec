<?php
namespace EC\PrincipalBundle\Entity;
 
use Doctrine\ORM\EntityRepository;
 
class ComunidadRepository extends EntityRepository
{
    public function findAllByDni($admin)
    {
    	  $dni=$admin->getDni();
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.administrador=$dni"
            )
            ->getResult();
    }
    
    public function findByCif($cif)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM ECPrincipalBundle:Comunidad c WHERE c.cif=$cif"
            )
            ->getResult();
    }
}
?>