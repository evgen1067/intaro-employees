<?php

namespace App\Services;

use App\Exception\ApiException;
use JMS\Serializer\SerializerInterface;

class BitrixService
{
    private array $methods = [
        'user' => 'user.search',
        'departments' => 'department.get',
    ];

    public function __construct(
        private ApiService $apiService,
        SerializerInterface $serializer,
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function getDepartments(): array
    {
        $departmentList = [];
        $departmentsBitrix = $this->get($this->methods['departments']);
        foreach ($departmentsBitrix as $d) {
            $departmentList[$d['ID']] = $d['NAME'];
        }

        return $departmentList;
    }

    /**
     * @throws ApiException
     * @throws \JsonException
     * @throws \Exception
     */
    public function getUsers(): array
    {
        $usersBitrix = $this->get($this->methods['user']);
        $usersList = [];
        foreach ($usersBitrix as $u) {
            $usersList[] = [
                'id' => $u['ID'],
                'name' => $u['LAST_NAME'].' '.$u['NAME'].' '.($this->checkField($u, 'SECOND_NAME') ? $u['SECOND_NAME'] : ''),
                'gender' => $this->checkField($u, 'PERSONAL_GENDER') ? ('M' === $u['PERSONAL_GENDER'] ? 1 : 2) : 3,
                'dateOfBirth' => $this->checkField($u, 'PERSONAL_BIRTHDAY') ? new \DateTimeImmutable($u['PERSONAL_BIRTHDAY']) : null,
                'dateOfEmployment' => $this->checkField($u, 'DATE_REGISTER') ? new \DateTimeImmutable($u['DATE_REGISTER']) : null,
                'position' => $this->checkField($u, 'WORK_POSITION') ? $u['WORK_POSITION'] : 'не указано',
                'status' => isset($u['ACTIVE']) ? ($u['ACTIVE'] ? 1 : 3) : 4,
                'dateOfDismissal' => isset($u['ACTIVE']) ? ($u['ACTIVE'] ? null : new \DateTimeImmutable($u['LAST_LOGIN'])) : null,
                'reasonOfDismissal' => null,
                'categoryOfDismissal' => null,
                'departments' => $u['UF_DEPARTMENT'],
                'MAP_NAME' => $u['LAST_NAME'].' '.$u['NAME'],
            ];
        }

        return $usersList;
    }

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    private function get(string $method): array
    {
        $result = [];
        $next = 0;
        do {
            $response = $this->apiService->get(
                $this->buildUrl($method),
                ['sort' => 'ID', 'order' => 'ASC', 'start' => $next]
            );
            $jsonResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            if (isset($jsonResponse['error'])) {
                throw new ApiException($jsonResponse['error_description']);
            }
            if ([] != $jsonResponse['result']) {
                $result = array_merge($result, $jsonResponse['result']);
            }
            if (isset($jsonResponse['next'])) {
                $next = $jsonResponse['next'];
            } else {
                break;
            }
        } while ([] != $jsonResponse);

        return $result;
    }

    private function buildUrl(string $method): string
    {
        return $_ENV['BITRIX_URL'].$_ENV['BITRIX_TOKEN'].'/'.$method;
    }

    private function checkField(array $array, string $field): bool
    {
        return isset($array[$field]) && strlen($array[$field]) > 0;
    }
}
