<?php

namespace App\Repository;

use App\Entity\HiringPlan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HiringPlan>
 *
 * @method HiringPlan|null find($id, $lockMode = null, $lockVersion = null)
 * @method HiringPlan|null findOneBy(array $criteria, array $orderBy = null)
 * @method HiringPlan[]    findAll()
 * @method HiringPlan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HiringPlanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HiringPlan::class);
    }

    public function save(HiringPlan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HiringPlan $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws Exception
     */
    public function getList(array $companies, array|null $filter, array|null $sort): array
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = "
            select
                hp.id as id, 
                hp.status as status, 
                hp.position as position, 
                hp.expected_count as expected_count, 
                hp.urgency as urgency, 
                hp.director as director, 
                hp.offers_count as offers_count, 
                hp.employees_count as employees_count, 
                hp.comment as comment,
                mu.name as manager_name
            from hiring_plan hp
                inner join hiring_plan_user hpu 
                    on hp.id = hpu.hiring_plan_id
                inner join manager_user mu 
                    on mu.id = hpu.user_id
                inner join user_company uc 
                    on mu.id = uc.user_id
                inner join company c 
                    on c.id = uc.company_id where c.id in (";
        foreach ($companies as $company) {
            $sql .= $company . ",";
        }
        $sql = mb_substr($sql, 0, strlen($sql) - 1) . ")";

        $params = [];
        if (null !== $filter && count($filter) > 0) {
            foreach ($filter as $key => $item) {
                if (isset($item['type'])) {
                    $type = $item['type'];
                    if ('text_contains' === $type) {
                        $sql .= " and lower(hp.".$key.") like :param_".$key;
                        $params[":param_".$key] = "%".mb_strtolower($item['value'])."%";
                    }

                    if ('text_not_contains' === $type) {
                        $sql .= " and lower(hp.".$key.") not like :param_".$key;
                        $params[":param_".$key] = "%".mb_strtolower($item['value'])."%";
                    }

                    if ('text_start' === $type) {
                        $sql .= " and lower(hp.".$key.") like :param_".$key;
                        $params[":param_".$key] = mb_strtolower($item['value'])."%";
                    }

                    if ('text_end' === $type) {
                        $sql .= " and lower(hp.".$key.") like :param_".$key;
                        $params[":param_".$key] = "%".mb_strtolower($item['value']);
                    }

                    if (in_array($type, ['text_accuracy', 'list', 'number_equal'])) {
                         if ($key === 'manager_name') {
                             $sql .= " and mu.id = :param_".$key;
                             $params[":param_".$key] = $item['value'];
                         } else {
                             $sql .= " and hp.".$key." = :param_".$key;
                             $params[":param_".$key] = $item['value'];
                         }
                    }

                    if ('number_not_equal' === $type) {
                        $sql .= " and hp." . $key . " != :param_".$key;
                        $params[":param_".$key] = $item['value'];
                    }

                    if ('number_inequality' === $type) {
                        $valueFrom = $item['valueFrom'] ?? '';
                        $valueTo = $item['valueTo'] ?? '';

                        if (isset($item['isStrict'])) {
                            if ('' !== $valueFrom && '' === $valueTo) {
                                if (true === $item['isStrict']) {
                                    $sql .= " and hp." . $key . " > :param_from".$key;
                                } elseif (false === $item['isStrict']) {
                                    $sql .= " and hp." . $key . " >= :param_from".$key;
                                }
                                $params[":param_from".$key] = $valueFrom;
                            } elseif ('' !== $valueTo && '' === $valueFrom) {
                                if (true === $item['isStrict']) {
                                    $sql .= " and hp." . $key . " < :param_to".$key;
                                } else {
                                    $sql .= " and hp." . $key . " <= :param_to".$key;
                                }
                                $params[":param_to".$key] = $valueTo;
                            }
                        } elseif ('' !== $valueTo && '' !== $valueFrom) {
                            if (true === $item['isStrict']) {
                                $sql .= " and hp." . $key . " > :param_from".$key;
                                $sql .= " and hp." . $key . " < :param_to".$key;

                            } else {
                                $sql .= " and hp." . $key . " >= :param_from".$key;
                                $sql .= " and hp." . $key . " <= :param_to".$key;
                            }
                            $params[":param_from".$key] = $valueFrom;
                            $params[":param_to".$key] = $valueTo;
                        }
                    }
                }
            }
        }

        if (null !== $sort) {
            $key = $sort['key'];
            if ($key === 'manager_name') {
                $sql .= " order by mu.name " . $sort['value'];
            } else {
                $sql .= " order by hp." . $key . " " . $sort['value'];
            }
        }

        $sql .= " group by mu.id, hp.id";

        $result = $conn->prepare($sql)->executeQuery($params)->fetchAllAssociative();
        return HiringPlan::mapSqlArray($result);
    }

    /**
     * @throws Exception
     */
    public function getRecord(int $id, array $companies)
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $sql = "
            select
                hp.id as id, 
                hp.status as status, 
                hp.position as position, 
                hp.expected_count as expected_count, 
                hp.urgency as urgency, 
                hp.director as director, 
                hp.offers_count as offers_count, 
                hp.employees_count as employees_count, 
                hp.comment as comment,
                mu.id as manager_name
            from hiring_plan hp
                inner join hiring_plan_user hpu 
                    on hp.id = hpu.hiring_plan_id
                inner join manager_user mu 
                    on mu.id = hpu.user_id
                inner join user_company uc 
                    on mu.id = uc.user_id
                inner join company c 
                    on c.id = uc.company_id and c.id in (";
        foreach ($companies as $company) {
            $sql .= $company . ",";
        }
        $sql = mb_substr($sql, 0, strlen($sql) - 1) . ")";
        $sql .= " where hp.id = :hpId group by mu.id, hp.id";

        $result = $conn->prepare($sql)->executeQuery(['hpId' => $id])->fetchAllAssociative()[0];
        $result['status'] = (int) $result['status'];
        $result['urgency'] = (int) $result['urgency'];
        $result['manager_name'] = (int) $result['manager_name'];
        return $result;
    }
}
