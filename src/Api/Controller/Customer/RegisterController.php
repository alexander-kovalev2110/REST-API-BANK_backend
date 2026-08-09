<?php

namespace App\Api\Controller\Customer;

use App\Application\Customer\Command\RegisterCustomerCommand;
use App\Application\Customer\DTO\RegisterRequest;
use App\Application\Customer\DTO\AuthView;
use App\Application\Customer\Service\TokenServiceInterface;
use App\Infrastructure\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RegisterController extends AbstractController
{
    #[Route('/customers/register', name: 'create_customer', methods: ['POST'])]
    public function __invoke(
        RegisterRequest $request,
        CommandBus $commandBus,
        TokenServiceInterface $tokenService
    ): JsonResponse {
        $customer = $commandBus->dispatch(
            new RegisterCustomerCommand($request->name, $request->password)
        );

        $token = $tokenService->createToken($customer);

        return $this->json(new AuthView($token), Response::HTTP_CREATED);
    }
}
