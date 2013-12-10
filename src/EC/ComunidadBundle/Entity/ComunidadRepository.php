<?php
namespace EC\ComunidadBundle\Entity;
 
use Doctrine\ORM\EntityRepository;
 
class ComunidadRepository extends EntityRepository
{
    public function findAllByNColegiado($admin)
    {
    	  $ncolegiado=$admin->getNColegiado();
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM ECComunidadBundle:Comunidad c WHERE c.administrador=$ncolegiado"
            )
            ->getResult();
    }
    
    public function findByCif($cif)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM ECComunidadBundle:Comunidad c WHERE c.cif=$cif"
            )
            ->getResult();
    }
}
?>