<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Repositories\CoasterRepository;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Symfony\Component\Serializer\SerializerInterface;

class CoasterController extends BaseController
{
    private CoasterRepository $coasterRepository;
    private SerializerInterface $serializer;

    public function __construct()
    {
        $this->coasterRepository = new CoasterRepository();
        $this->serializer = Services::serializer();
    }

    public function create(): ResponseInterface
    {
        $rules = [
            'staff_count' => 'required|integer|greater_than[0]',
            'customers_count' => 'required|integer|greater_than[0]',
            'length' => 'required|integer|greater_than[0]',
            'opening_time' => 'required|valid_time',
            'closing_time' => 'required|valid_time'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            $coaster = $this->coasterRepository->create(
                $this->validator->getValidated()
            );

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $this->serializer->normalize($coaster)
            ]);
        } catch (\Throwable $error) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $error->getMessage()
            ]);
        }
    }

    public function update(string $coasterId): ResponseInterface
    {
        $rules = [
            'staff_count' => 'integer|greater_than[0]',
            'customers_count' => 'integer|greater_than[0]',
            'opening_time' => 'valid_time',
            'closing_time' => 'valid_time'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            $coaster = $this->coasterRepository->update(
                $coasterId,
                $this->validator->getValidated()
            );

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $this->serializer->normalize($coaster)
            ]);
        } catch (\Throwable $error) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $error->getMessage()
            ]);
        }
    }

    public function addWagon(string $coasterId): ResponseInterface
    {
        $rules = [
            'capacity' => 'required|integer|greater_than[0]',
            'speed' => 'required|numeric|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors()
            ]);
        }

        try {
            $coaster = $this->coasterRepository->addWagon(
                $coasterId,
                $this->validator->getValidated()
            );

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $this->serializer->normalize($coaster)
            ]);
        } catch (\Throwable $error) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $error->getMessage()
            ]);
        }
    }

    public function removeWagon(string $coasterId, string $wagonId): ResponseInterface
    {
        try {
            $coaster = $this->coasterRepository->removeWagon($coasterId, $wagonId);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $this->serializer->normalize($coaster)
            ]);
        } catch (\Throwable $error) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => $error->getMessage()
            ]);
        }
    }
}
