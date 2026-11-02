<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdvisoryMembership>
 */
final class DoctrineAdvisoryMembershipRepository extends ServiceEntityRepository implements AdvisoryMembershipRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdvisoryMembership::class);
    }

    public function save(AdvisoryMembership $membership): void
    {
        $this->getEntityManager()->persist($membership);
    }

    public function findForAccountAndFirm(Account $account, string $advisoryFirmId): ?AdvisoryMembership
    {
        return $this->findOneBy([
            'account' => $account,
            'advisoryFirm' => $advisoryFirmId,
        ]);
    }

    public function findForFirmAndEmail(AdvisoryFirm $advisoryFirm, string $email): ?AdvisoryMembership
    {
        foreach ($this->findForFirm($advisoryFirm) as $membership) {
            if ($membership->account()->email() === $email) {
                return $membership;
            }
        }

        return null;
    }

    public function findForAccount(Account $account): array
    {
        return $this->findBy(['account' => $account]);
    }

    public function findForFirm(AdvisoryFirm $advisoryFirm): array
    {
        return $this->findBy(['advisoryFirm' => $advisoryFirm]);
    }
}
