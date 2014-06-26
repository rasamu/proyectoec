<?php
namespace EC\PrincipalBundle\Entity;
 
use Doctrine\ORM\EntityRepository;
use EC\PrincipalBundle\Entity\Comunidad;
 
class BloqueRepository extends EntityRepository
{
    public function findByCif($cif)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT c FROM ECPrincipalBundle:Bloque c WHERE c.cif=$cif"
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