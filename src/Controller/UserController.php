<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Repository\DepartmentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/v1/users')]
class UserController extends AbstractController
{
    #[Route('/auth', name: 'api_auth', methods: ['POST'])]
    public function auth()
    {
    }

    #[Route('/profile', name: 'api_profile', methods: ['GET'])]
    public function profile(): JsonResponse
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

        return new JsonResponse([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ], Response::HTTP_OK);
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $hasher,
        UserRepository $repo,
        CompanyRepository $companyRepo,
        DepartmentRepository $departmentRepo
    ): JsonResponse {
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

        if ($this->isGranted(User::ROLE_SUPER_ADMIN) || $this->isGranted(User::ROLE_TOP_MANAGER)) {
            $userRequest = json_decode($request->getContent(), true);
            $user = $repo->findOneBy(['email' => $userRequest['email']]);
            if (null !== $user) {
                return new JsonResponse([
                    'code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Пользователь с таким email существует.',
                ], Response::HTTP_BAD_REQUEST);
            }
            $user = new User();
            $user
                ->setEmail($userRequest['email'])
                ->setName($userRequest['name'])
                ->setRoles([$userRequest['roles']]);
            $password = $hasher->hashPassword(
                $user, $userRequest['password']
            );
            $user->setPassword($password);
            if ($userRequest['roles'] === User::ROLE_HR_MANAGER) {
                $companies = $userRequest['companies'];
                foreach ($companies as $companyId) {
                    $company = $companyRepo->find($companyId);
                    if (null !== $company) {
                        $user->addCompany($company);
                    }
                }
            } else if ($userRequest['roles'] === User::ROLE_DEPARTMENT_MANAGER) {
                $departments = $userRequest['departments'];
                foreach ($departments as $departmentId) {
                    $department = $departmentRepo->find($departmentId);
                    if (null !== $department) {
                        $user->addDepartment($department);
                    }
                }
            }
            $repo->save($user, true);
            return new JsonResponse([
                'code' => Response::HTTP_CREATED,
                'message' => 'Пользователь успешно создан.',
            ], Response::HTTP_CREATED);
        } else {
            return new JsonResponse([
                'code' => Response::HTTP_METHOD_NOT_ALLOWED,
                'message' => 'У пользователя недостаточно прав.',
            ], Response::HTTP_METHOD_NOT_ALLOWED);
        }
    }
}
