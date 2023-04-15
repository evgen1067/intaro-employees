<?php

namespace App\Services;

use App\Entity\EvolutionEmployee;
use App\Exception\ApiException;

class EvolutionService
{
    public function __construct(
        private ApiService $apiService,
    ) {}

    /**
     * @throws ApiException
     * @throws \JsonException
     */
    public function getUsers(): array
    {
        $response = $this->apiService->get(
            $_ENV['EVOLUTION_URL'],
            ['token' => $_ENV['EVOLUTION_TOKEN']]
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
}