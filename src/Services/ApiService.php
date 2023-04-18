<?php

namespace App\Services;

use App\Exception\ApiException;

class ApiService
{
    /**
     * @throws ApiException
     */
    public function get(
        string $route,
        $getParams = null,
        $headers = null,
        $exceptionMessage = 'Сервис временно недоступен.'
    ): bool|string {
        $route = $route.((null !== $getParams) ? '?'.http_build_query($getParams) : '');
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ((null !== $headers) ? $headers : ['Content-Type: application/json']),
        ];
        $query = curl_init($route);
        curl_setopt_array($query, $options);
        $response = curl_exec($query);

        if (false === $response) {
            throw new ApiException($exceptionMessage);
        }
        curl_close($query);

        return $response;
    }

    /**
     * @throws ApiException
     */
    public function post(
        string $route,
        $postParams = null,
        $headers = null,
        $exceptionMessage = 'Сервис временно недоступен.'
    ): bool|string {
        $options = [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => (null !== $headers ? $headers : ['Content-Type: application/json']),
            CURLOPT_POSTFIELDS => (null !== $postParams ? $postParams : ''),
        ];
        $query = curl_init($route);
        curl_setopt_array($query, $options);
        $response = curl_exec($query);

        if (false === $response) {
            throw new ApiException($exceptionMessage);
        }
        curl_close($query);

        return $response;
    }
}
