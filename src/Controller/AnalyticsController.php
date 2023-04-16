<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\AnalyticsService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/analytics')]
class AnalyticsController extends AbstractController
{
    #[Route('/dismissal', name: 'app_analytics_dismissal')]
    public function dismissal(Request $request, EntityManagerInterface $em): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Пользователь не авторизован.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $roleFilters = [
            'companies' => [],
            'departments' => [],
        ];

        if ($this->isGranted(User::ROLE_HR_MANAGER)) {
            $companies = $user->getCompanies();
            foreach ($companies as $company) {
                $roleFilters['companies'][] = $company->getId();
            }
        }

        if ($this->isGranted(User::ROLE_DEPARTMENT_MANAGER)) {
            $departments = $user->getDepartments();
            foreach ($departments as $department) {
                $roleFilters['departments'][] = $department->getId();
            }
        }
        $valueFrom = $request->query->get('valueFrom');
        $valueFrom = \DateTimeImmutable::createFromFormat('d.m.Y', $valueFrom);
        $valueTo = $request->query->get('valueTo');
        $valueTo = \DateTimeImmutable::createFromFormat('d.m.Y', $valueTo);
        $analytics = new AnalyticsService($valueFrom, $valueTo, $em);
        try {
            return new JsonResponse($analytics->getDismissal($roleFilters), Response::HTTP_OK);
        } catch (Exception|\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }

    #[Route('/turnover', name: 'app_analytics_turnover')]
    public function turnover(Request $request, EntityManagerInterface $em): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'code' => Response::HTTP_UNAUTHORIZED,
                'message' => 'Пользователь не авторизован.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $roleFilters = [
            'companies' => [],
            'departments' => [],
        ];

        if ($this->isGranted(User::ROLE_HR_MANAGER)) {
            $companies = $user->getCompanies();
            foreach ($companies as $company) {
                $roleFilters['companies'][] = $company->getId();
            }
        }

        if ($this->isGranted(User::ROLE_DEPARTMENT_MANAGER)) {
            $departments = $user->getDepartments();
            foreach ($departments as $department) {
                $roleFilters['departments'][] = $department->getId();
            }
        }
        $valueFrom = $request->query->get('valueFrom');
        $valueFrom = \DateTimeImmutable::createFromFormat('d.m.Y', $valueFrom);
        $valueTo = $request->query->get('valueTo');
        $valueTo = \DateTimeImmutable::createFromFormat('d.m.Y', $valueTo);
        $analytics = new AnalyticsService($valueFrom, $valueTo, $em);
        try {
            return new JsonResponse($analytics->getTurnover($roleFilters), Response::HTTP_OK);
        } catch (Exception|\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }
}
