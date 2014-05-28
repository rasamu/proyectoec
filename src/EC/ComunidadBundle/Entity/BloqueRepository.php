<?php
namespace EC\ComunidadBundle\Entity;
 
use Doctrine\ORM\EntityRepository;
use EC\ComunidadBundle\Entity\Comunidad;
 
class BloqueRepository extends EntityRepository
{
    public function findByCif($cif)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM ECComunidadBundle:Bloque c WHERE c.cif=$cif"
            )
            ->getResult();
    }
    
    public function getBloques($comunidad)
    {
        $qb = $this->getBloquesQueryBuilder($comunidad);
    	 return  $qb->getQuery()->getResult();
    }
    
    public function getBloquesQueryBuilder($comunidad)
	{
   	 return $this->createQueryBuilder('b')
        	->where('b.comunidad = :id')
        	->setParameter('id', $comunidad->getId());
	}
}
?>