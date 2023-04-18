<?php

namespace App\Controller;

use App\Entity\HiringPlan;
use App\Entity\User;
use App\Repository\HiringPlanRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/v1/hiring')]
class HiringController extends AbstractController
{
    #[Route('/list', methods: ['GET'])]
    public function list(
        Request $request,
        HiringPlanRepository $repo,
        PaginatorInterface $paginator
    ): JsonResponse {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Пользователь не авторизован.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($this->isGranted(User::ROLE_HR_MANAGER) || $this->isGranted(User::ROLE_SUPER_ADMIN)) {
            try {
                $page = $request->query->get('page', 1);
                $limit = $request->query->get('limit', 10);

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

                $companiesIds = [];
                $companies = $user->getCompanies();
                foreach ($companies as $company) {
                    $companiesIds[] = $company->getId();
                }
                $listResponse = $repo->getList($companiesIds, $filter, $sort);

                if ('Все' !== $limit) {
                    // пагинация
                    $listResponse = $paginator->paginate(
                        $listResponse,
                        $page,
                        $limit
                    );
                    // отбор нужных полей
                    $listResponse = [
                        'items' => $listResponse->getItems(),
                        'totalCount' => $listResponse->getTotalItemCount(),
                    ];
                } else {
                    $listResponse = [
                        'items' => $listResponse,
                        'totalCount' => count($listResponse),
                    ];
                }

                return new JsonResponse([
                    'status' => true,
                    'totalCount' => $listResponse['totalCount'],
                    'data' => $listResponse['items'],
                ], Response::HTTP_OK);
            } catch (Exception|\JsonException $e) {
                return new JsonResponse([
                    'status' => false,
                    'data' => $e->getMessage(),
                ], Response::HTTP_OK);
            }
        }

        return new JsonResponse([
            'status' => false,
            'data' => 'У вас недостаточно прав.',
        ], Response::HTTP_OK);
    }

    #[Route('/managers', methods: ['GET'])]
    public function managers(UserRepository $repo): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Пользователь не авторизован.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            return new JsonResponse([
                'status' => true,
                'data' => $repo->names(),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage(),
            ], Response::HTTP_OK);
        }
    }

    #[Route('/new', methods: ['POST'])]
    public function new(
        Request $request,
        HiringPlanRepository $repo
    ): JsonResponse {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Пользователь не авторизован.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $hp = new HiringPlan();
            $hp->fromJson($json)
                ->addManager($user);
            $repo->save($hp, true);

            return new JsonResponse([
                'status' => true,
                'data' => 'Запись успешно добавлена.',
            ], Response::HTTP_OK);
        } catch (\JsonException $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage(),
            ], Response::HTTP_OK);
        }
    }

    #[Route('/edit/{id}', methods: ['POST'])]
    public function edit(
        int $id,
        Request $request,
        HiringPlanRepository $repo
    ): JsonResponse {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Пользователь не авторизован.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        try {
            $json = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $hp = $repo->find($id);
            if (null !== $hp) {
                $hp->fromJson($json)
                    ->addManager($user);
                $repo->save($hp, true);

                return new JsonResponse([
                    'status' => true,
                    'data' => 'Запись успешно обновлена.',
                ], Response::HTTP_OK);
            } else {
                return new JsonResponse([
                    'status' => false,
                    'data' => 'Запись с таким id не найдена.',
                ], Response::HTTP_OK);
            }
        } catch (\JsonException $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage(),
            ], Response::HTTP_OK);
        }
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(int $id, HiringPlanRepository $repo): JsonResponse
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Пользователь не авторизован.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($this->isGranted(User::ROLE_HR_MANAGER) || $this->isGranted(User::ROLE_SUPER_ADMIN)) {
            $companiesIds = [];
            $companies = $user->getCompanies();
            foreach ($companies as $company) {
                $companiesIds[] = $company->getId();
            }
            $hp = $repo->find($id);
            if (null !== $hp) {
                try {
                    $item = $repo->getRecord($id, $companiesIds);

                    return new JsonResponse([
                        'status' => true,
                        'data' => $item,
                    ], Response::HTTP_OK);
                } catch (Exception $e) {
                    return new JsonResponse([
                        'status' => false,
                        'data' => $e->getMessage(),
                    ], Response::HTTP_OK);
                }
            } else {
                return new JsonResponse([
                    'status' => false,
                    'data' => 'Запись с таким id не найдена.',
                ], Response::HTTP_OK);
            }
        }

        return new JsonResponse([
            'status' => false,
            'data' => 'У вас недостаточно прав.',
        ], Response::HTTP_OK);
    }

    #[Route('/delete', methods: ['POST'])]
    public function delete(Request $request, HiringPlanRepository $repo): JsonResponse
    {
        try {
            $idsForDelete = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);
            foreach ($idsForDelete as $id) {
                $e = $repo->find($id);
                if ($e) {
                    $repo->remove($repo->find($id), true);

                    continue;
                }

                return new JsonResponse([
                    'status' => false,
                    'data' => 'Записи не найдены.',
                ], Response::HTTP_OK);
            }

            return new JsonResponse([
                'status' => true,
                'data' => 'Записи успешно удалена.',
            ], Response::HTTP_OK);
        } catch (\JsonException $e) {
            return new JsonResponse([
                'status' => false,
                'data' => $e->getMessage(),
            ], Response::HTTP_OK);
        }
    }
}
