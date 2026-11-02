<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Contact;
use App\Entity\FiscalEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 */
final class DoctrineContactRepository extends ServiceEntityRepository implements ContactRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function save(Contact $contact): void
    {
        $this->getEntityManager()->persist($contact);
    }

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?Contact
    {
        $contact = $this->createQueryBuilder('c')
            ->andWhere('c.id = :id')
            ->andWhere('c.fiscalEntity = :entity')
            ->setParameter('id', $id)
            ->setParameter('entity', $fiscalEntity)
            ->getQuery()
            ->getOneOrNullResult();

        return $contact instanceof Contact ? $contact : null;
    }

    public function findByTaxId(FiscalEntity $fiscalEntity, string $taxId): ?Contact
    {
        $contact = $this->createQueryBuilder('c')
            ->andWhere('c.fiscalEntity = :entity')
            ->andWhere('c.taxId = :taxId')
            ->setParameter('entity', $fiscalEntity)
            ->setParameter('taxId', $taxId)
            ->getQuery()
            ->getOneOrNullResult();

        return $contact instanceof Contact ? $contact : null;
    }

    public function search(FiscalEntity $fiscalEntity, string $query): array
    {
        $builder = $this->createQueryBuilder('c')
            ->andWhere('c.fiscalEntity = :entity')
            ->setParameter('entity', $fiscalEntity)
            ->orderBy('c.displayName', 'ASC');
        if ('' !== trim($query)) {
            $builder->andWhere('c.displayName LIKE :query OR c.taxId LIKE :query')
                ->setParameter('query', SearchTerm::like($query));
        }

        return $this->onlyContacts($builder->getQuery()->getResult());
    }

    /**
     * @return list<Contact>
     */
    private function onlyContacts(mixed $rows): array
    {
        $contacts = [];
        if (!is_array($rows)) {
            return $contacts;
        }
        foreach ($rows as $row) {
            if ($row instanceof Contact) {
                $contacts[] = $row;
            }
        }

        return $contacts;
    }
}
