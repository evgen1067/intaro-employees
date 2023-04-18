<?php

namespace App\Services;

use App\Exception\ApiException;

class EvolutionService
{
    private array $methods = [
        'employee' => 'employee',
        'auth' => 'auth',
    ];

    public function __construct(
        private ApiService $apiService,
    ) {
    }

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function getUsers(string $token): array
    {
        $response = $this->apiService->get(
            $this->buildUrl($this->methods['employee'], null),
            ['token' => $token]
        );
        $jsonResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        if (isset($jsonResponse['error'])) {
            throw new ApiException($jsonResponse['error_description']);
        }
        $data = $jsonResponse['data'];
        foreach ($data as $key => $item) {
            $data[$key]['company'] = strlen($item['company']) > 0 ? $item['company'] : 'не указано';
        }

        return $data;
    }

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function auth(): string
    {
        $response = $this->apiService->post(
            $this->buildUrl(
                $this->methods['auth'],
                ['username' => $_ENV['EVOLUTION_USERNAME'], 'password' => $_ENV['EVOLUTION_PASSWORD']]
            ),
            [],
            ['Content-Type: application/x-www-form-urlencoded']
        );
        $jsonResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        if (isset($jsonResponse['status'])) {
            throw new ApiException($jsonResponse['status']);
        }

        return $jsonResponse['token'];
    }

    private function buildUrl(string $method, array|null $params): string
    {
        return $_ENV['EVOLUTION_URL'].'/'.$method.(null !== $params ? ('?'.http_build_query($params)) : '');
    }
}
