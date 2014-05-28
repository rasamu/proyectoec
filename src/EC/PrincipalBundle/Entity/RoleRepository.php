<?php

namespace EC\PrincipalBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
* RoleRepository
*/
class RoleRepository extends EntityRepository
{
    public function findById($id)
    {
        return $this->getEntityManager()->createQuery("
			SELECT role.id as id
			FROM ECPrincipalBundle:Role role
			WHERE role.id LIKE :$id
			")->getResult();

    }
}