<?php

namespace App\Controller;

use App\DTO\RegisterRequest;
use App\DTO\LoginRequest;
use App\DTO\AuthResponse;
use App\Service\CustomerService;
use App\Service\TokenService;
use App\Service\ValidatorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CustomerController extends AbstractController
{
    public function __construct(
        private readonly CustomerService $customerService,
        private readonly TokenService $tokenService,
        private readonly ValidatorService $validatorService,
        private readonly SerializerInterface $serializer,
    ) {}

    // REGESTERING A NEW CLIENT
    #[Route('/customers/register', name: 'create_customer', methods: ['POST'])]
    public function createCustomer(Request $request): JsonResponse
    {
        // Deserializing a JSON request body into a DTO
        /** @var RegisterRequest $dto */
        $dto =$this->serializer->deserialize(
            $request->getContent(),     // JSON string
            RegisterRequest::class,     // Target class (type)
            'json'                      // Data format
        );

        $this->validatorService->validate($dto);

        $customer = $this->customerService->create($dto);
        $token = $this->tokenService->createToken($customer);

        return $this->json(new AuthResponse($token), Response::HTTP_CREATED);   
    }

    // CLIENT AUTHENTICATION
    #[Route('/customers/login', name: 'login_customer', methods: ['POST'])]
    public function loginCustomer(Request $request): JsonResponse
    {
        /** @var LoginRequest $dto */
        $dto =$this->serializer->deserialize(
            $request->getContent(), 
            LoginRequest::class, 
            'json'
        );

        $this->validatorService->validate($dto);

        $customer = $this->customerService->login($dto);
        $token = $this->tokenService->createToken($customer);

        return $this->json(new AuthResponse($token), Response::HTTP_OK);
    }
}
