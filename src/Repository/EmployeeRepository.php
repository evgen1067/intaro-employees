<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employee>
 *
 * @method null|Employee find($id, $lockMode = null, $lockVersion = null)
 * @method null|Employee findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    public function save(Employee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Employee $entity, bool $flush = false): void
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

        $q = 'delete from employee';

        $conn->prepare($q)->executeQuery([]);
    }

    /**
     * @throws Exception
     */
    public function table(array|null $filter, array|null $sort, array $roleFilters): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "
        select
            e.id as id,
            e.name as name,
            e.gender as gender,
            e.date_of_birth as date_of_birth,
            e.date_of_employment as date_of_employment,
            coalesce(c.name, 'не указано') as company,
            coalesce(string_agg(d.name, ', '), 'не указано') as departments,
            e.position as position,
            e.status as status,
            e.competence as competence,
            e.grade as grade,
            e.date_of_dismissal as date_of_dismissal,
            e.reason_of_dismissal as reason_of_dismissal,
            e.category_of_dismissal as category_of_dismissal
            from employee e
                inner join company c 
                    on c.id = e.company_id
                inner join employee_department ed 
                    on e.id = ed.employee_id
                inner join department d 
                    on d.id = ed.department_id
            ";
        $params = [];
        if (null !== $filter && count($filter) > 0) {
            foreach ($filter as $key => $item) {
                if (isset($item['type'])) {
                    $type = $item['type'];
                    if ('text_contains' === $type) {
                        $sql .= ' and lower(e.'.$key.') like :param_'.$key;
                        $params[':param_'.$key] = '%'.mb_strtolower($item['value']).'%';
                    }

                    if ('text_not_contains' === $type) {
                        $sql .= ' and lower(e.'.$key.') not like :param_'.$key;
                        $params[':param_'.$key] = '%'.mb_strtolower($item['value']).'%';
                    }

                    if ('text_start' === $type) {
                        $sql .= ' and lower(e.'.$key.') like :param_'.$key;
                        $params[':param_'.$key] = mb_strtolower($item['value']).'%';
                    }

                    if ('text_end' === $type) {
                        $sql .= ' and lower(e.'.$key.') like :param_'.$key;
                        $params[':param_'.$key] = '%'.mb_strtolower($item['value']);
                    }

                    if (('text_accuracy' === $type || 'list' === $type)) {
                        if ('departments' === $key) {
                            $sql .= ' and d.id = :param_'.$key;
                            $params[':param_'.$key] = $item['value'];
                        } elseif ('company' === $key) {
                            $sql .= ' and c.id = :param_'.$key;
                            $params[':param_'.$key] = $item['value'];
                        } else {
                            $sql .= ' and e.'.$key.' = :param_'.$key;
                            $params[':param_'.$key] = $item['value'];
                        }
                    }

                    if ('date_day' === $type) {
                        $sql .= ' and e.'.$key.' = :param_'.$key;
                        $params[':param_'.$key] = \DateTimeImmutable::createFromFormat('d.m.Y', $item['value'])->format('Y-m-d');
                    }

                    if ('date_before' === $type) {
                        $sql .= ' and e.'.$key.' < :param_'.$key;
                        $params[':param_'.$key] = \DateTimeImmutable::createFromFormat('d.m.Y', $item['value'])->format('Y-m-d');
                    }

                    if ('date_after' === $type) {
                        $sql .= ' and e.'.$key.' > :param_'.$key;
                        $params[':param_'.$key] = \DateTimeImmutable::createFromFormat('d.m.Y', $item['value'])->format('Y-m-d');
                    }
                }
            }
        }
        if (count($roleFilters['companies']) > 0) {
            $result = '';
            foreach ($roleFilters['companies'] as $companyId) {
                $result .= $companyId.',';
            }
            $result = mb_substr($result, 0, strlen($result) - 1);
            if (null === $filter || 0 === count($filter)) {
                $sql .= ' where c.id in ('.$result.')';
            } else {
                $sql .= ' and c.id in ('.$result.')';
            }
        }
        if (count($roleFilters['departments']) > 0) {
            $result = '';
            foreach ($roleFilters['departments'] as $departmentId) {
                $result .= $departmentId.',';
            }
            $result = mb_substr($result, 0, strlen($result) - 1);
            if (null === $filter || 0 === count($filter)) {
                $sql .= ' where d.id in ('.$result.')';
            } else {
                $sql .= ' and d.id in ('.$result.')';
            }
        }

        $sql .= ' group by e.id, d.id, c.id';

        if (null !== $sort && 'workExperience' !== $sort['key']) {
            $key = $sort['key'];
            if ('departments' === $key) {
                $sql .= ' order by d.name '.$sort['value'];
            } elseif ('company' === $key) {
                $sql .= ' order by c.name '.$sort['value'];
            } else {
                $sql .= ' order by e.'.$key.' '.$sort['value'];
            }
        }

        $result = $conn
            ->prepare($sql)
            ->executeQuery($params)
            ->fetchAllAssociative();

        $filterWork = $filter['workExperience'] ?? null;

        return Employee::mapSqlArray($result, $filterWork, $sort);
    }

    /**
     * @throws Exception
     */
    public function grades(array $roleFilters): array
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = '
        select 
            distinct 
            e.grade as grade
        from employee e            
            inner join employee_department ed on e.id = ed.employee_id
            inner join department d on ed.department_id = d.id
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
        $sql .= ' order by e.grade
        ';
        $mapped = [];
        $result = $conn->prepare($sql)->executeQuery([])->fetchAllAssociative();
        foreach ($result as $row) {
            $mapped[] = [
                'listValueId' => $row['grade'],
                'label' => $row['grade'],
            ];
        }

        return $mapped;
    }

    /**
     * @throws Exception
     */
    public function positions(array $roleFilters): array
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = '
        select 
            distinct
            e.position as position
            from employee e            
            inner join employee_department ed on e.id = ed.employee_id
            inner join department d on ed.department_id = d.id
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
        $sql .= ' order by e.position';
        $mapped = [];
        $result = $conn->prepare($sql)->executeQuery([])->fetchAllAssociative();
        foreach ($result as $row) {
            $mapped[] = [
                'listValueId' => $row['position'],
                'label' => $row['position'],
            ];
        }

        return $mapped;
    }

    /**
     * @throws Exception
     */
    public function competences(array $roleFilters): array
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = '
        select 
            distinct
            e.competence as competence
        from employee e
            inner join employee_department ed on e.id = ed.employee_id
            inner join department d on ed.department_id = d.id
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
        $sql .= ' order by e.competence';

        $mapped = [];
        $result = $conn->prepare($sql)->executeQuery([])->fetchAllAssociative();
        foreach ($result as $row) {
            $mapped[] = [
                'listValueId' => $row['competence'],
                'label' => $row['competence'],
            ];
        }

        return $mapped;
    }
}
