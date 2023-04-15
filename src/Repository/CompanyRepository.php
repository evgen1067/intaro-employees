<?php

namespace App\Repository;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Company>
 *
 * @method Company|null find($id, $lockMode = null, $lockVersion = null)
 * @method Company|null findOneBy(array $criteria, array $orderBy = null)
 * @method Company[]    findAll()
 * @method Company[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Company::class);
    }

    public function save(Company $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Company $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws Exception
     */
    public function removeAll(): void
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $sql = 'delete from company';

        $conn->prepare($sql)->executeQuery([]);
    }

    /**
     * @throws Exception
     */
    public function names(): array
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = '
        select 
            c.id as id,
            c.name as name
        from company c
        order by c.name
        ';
        $mapped = [];
        $result = $conn->prepare($sql)->executeQuery([])->fetchAllAssociative();
        foreach ($result as $row) {
            $mapped[] = [
                'listValueId' => $row['id'],
                'label' => $row['name']
            ];
        }
        return $mapped;
    }
}
