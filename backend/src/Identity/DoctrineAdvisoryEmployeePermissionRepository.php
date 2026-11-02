<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryEmployeePermission;
use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdvisoryEmployeePermission>
 */
final class DoctrineAdvisoryEmployeePermissionRepository extends ServiceEntityRepository implements AdvisoryEmployeePermissionRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdvisoryEmployeePermission::class);
    }

    public function save(AdvisoryEmployeePermission $permission): void
    {
        $this->getEntityManager()->persist($permission);
    }

    public function remove(AdvisoryEmployeePermission $permission): void
    {
        $this->getEntityManager()->remove($permission);
    }

    public function findForMembership(AdvisoryMembership $membership): ?AdvisoryEmployeePermission
    {
        return $this->findOneBy(['membership' => $membership]);
    }

    public function grantedMembershipIds(AdvisoryFirm $advisoryFirm): array
    {
        /** @var list<mixed> $ids */
        $ids = $this->createQueryBuilder('permission')
            ->select('membership.id')
            ->innerJoin('permission.membership', 'membership')
            ->andWhere('membership.advisoryFirm = :firm')
            ->setParameter('firm', $advisoryFirm)
            ->getQuery()
            ->getSingleColumnResult();

        return array_values(array_filter($ids, is_string(...)));
    }
}
