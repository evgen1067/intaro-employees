<?php

namespace App\Controller;

use App\Repository\CompanyRepository;
use App\Repository\DepartmentRepository;
use App\Repository\EmployeeRepository;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/employees')]
class EmployeeController extends AbstractController
{
    #[Route('/table', name: 'app_employees_table', methods: ['GET'])]
    public function table(
        Request $request,
        EmployeeRepository $repo,
        PaginatorInterface $paginator
    ): JsonResponse
    {
        $page = $request->query->get('page', 0);
        $limit = $request->query->get('limit', 10);

        try {
            $filter = $request->query->get('filter') ? json_decode(
                $request->query->get('filter'),
                true,
                512,
                JSON_THROW_ON_ERROR
            ) : null;

            $sort = $request->query->get('sort') ? json_decode(
                $request->query->get('sort'),
                true,
                512,
                JSON_THROW_ON_ERROR
            ) : null;

            $tableData = $repo->table($filter, $sort);

            if ('Все' !== $limit) {
                // пагинация
                $tableData = $paginator->paginate(
                    $tableData,
                    $page,
                    $limit
                );
                // отбор нужных полей
                $tableData = [
                    'items' => $tableData->getItems(),
                    'totalCount' => $tableData->getTotalItemCount(),
                ];
            } else {
                $tableData = [
                    'items' => $tableData,
                    'totalCount' => count($tableData),
                ];
            }

            return new JsonResponse([
                'status' => true,
                'totalCount' => $tableData['totalCount'],
                'data' => $tableData['items'],
            ], Response::HTTP_OK);
        } catch (Exception | \JsonException $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }

    #[Route('/companies', name: 'app_employees_companies', methods: ['GET'])]
    public function companies(CompanyRepository $repo): JsonResponse
    {
        try {
            return new JsonResponse([
                'status' => true,
                'data' => $repo->names(),
            ], Response::HTTP_OK);
        } catch (\Doctrine\DBAL\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }

    #[Route('/departments', name: 'app_employees_departments', methods: ['GET'])]
    public function departments(DepartmentRepository $repo): JsonResponse
    {
        try {
            return new JsonResponse([
                'status' => true,
                'data' => $repo->names(),
            ], Response::HTTP_OK);
        } catch (\Doctrine\DBAL\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }

    #[Route('/grades', name: 'app_employees_grades', methods: ['GET'])]
    public function grades(EmployeeRepository $repo): JsonResponse
    {
        try {
            return new JsonResponse([
                'status' => true,
                'data' => $repo->grades(),
            ], Response::HTTP_OK);
        } catch (\Doctrine\DBAL\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }

    #[Route('/competences', name: 'app_employees_competences', methods: ['GET'])]
    public function competences(EmployeeRepository $repo): JsonResponse
    {
        try {
            return new JsonResponse([
                'status' => true,
                'data' => $repo->competences(),
            ], Response::HTTP_OK);
        } catch (\Doctrine\DBAL\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }

    #[Route('/positions', name: 'app_employees_positions', methods: ['GET'])]
    public function positions(EmployeeRepository $repo): JsonResponse
    {
        try {
            return new JsonResponse([
                'status' => true,
                'data' => $repo->positions(),
            ], Response::HTTP_OK);
        } catch (\Doctrine\DBAL\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }
}
