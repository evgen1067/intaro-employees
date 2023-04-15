<?php

namespace App\Controller;

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
        $valueFrom = $request->query->get('valueFrom');
        $valueFrom = \DateTimeImmutable::createFromFormat('d.m.Y', $valueFrom);
        $valueTo = $request->query->get('valueTo');
        $valueTo = \DateTimeImmutable::createFromFormat('d.m.Y', $valueTo);
        $department = $request->query->get('department', null);
        $analytics = new AnalyticsService($valueFrom, $valueTo, null === $department ? [] : [$department], $em);
        try {
            return new JsonResponse($analytics->getDismissal(), Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }

    #[Route('/turnover', name: 'app_analytics_turnover')]
    public function turnover(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $valueFrom = $request->query->get('valueFrom');
        $valueFrom = \DateTimeImmutable::createFromFormat('d.m.Y', $valueFrom);
        $valueTo = $request->query->get('valueTo');
        $valueTo = \DateTimeImmutable::createFromFormat('d.m.Y', $valueTo);
        $department = $request->query->get('department', null);
        $analytics = new AnalyticsService($valueFrom, $valueTo, null === $department ? [] : [$department], $em);
        try {
            return new JsonResponse($analytics->getTurnover(), Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage()
            ], Response::HTTP_OK);
        }
    }
}
