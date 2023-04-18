<?php

namespace App\Repository;

use App\Entity\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Department>
 *
 * @method null|Department find($id, $lockMode = null, $lockVersion = null)
 * @method null|Department findOneBy(array $criteria, array $orderBy = null)
 * @method Department[]    findAll()
 * @method Department[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function save(Department $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Department $entity, bool $flush = false): void
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

        $sql = 'delete from employee_department';

        $conn->prepare($sql)->executeQuery([]);

        $sql = 'delete from department';

        $conn->prepare($sql)->executeQuery([]);
    }

    /**
     * @throws Exception
     */
    public function names(array $roleFilters): array
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = '
        select 
            distinct 
            d.id as id,
            d.name as name
        from department d
        inner join employee_department ed on d.id = ed.department_id
        inner join employee e on ed.employee_id = e.id
        inner join company c on e.company_id = c.id';
        if (count($roleFilters['companies']) > 0) {
            $result = '';
            foreach ($roleFilters['companies'] as $companyId) {
                $result .= $companyId.',';
            }
            $result = mb_substr($result, 0, strlen($result) - 1);
            $sql .= ' where c.id in ('.$result.')';
        }
        if (count($roleFilters['departments']) > 0) {
            $result = '';
            foreach ($roleFilters['departments'] as $departmentId) {
                $result .= $departmentId.',';
            }
            $result = mb_substr($result, 0, strlen($result) - 1);
            $sql .= ' where d.id in ('.$result.')';
        }
        $sql .= ' order by d.name';
        $mapped = [];
        $result = $conn->prepare($sql)->executeQuery([])->fetchAllAssociative();
        foreach ($result as $row) {
            $mapped[] = [
                'listValueId' => $row['id'],
                'label' => $row['name'],
            ];
        }

        return $mapped;
    }
}
