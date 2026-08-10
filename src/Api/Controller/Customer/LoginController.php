<?php

namespace App\Api\Controller\Customer;

use App\Application\Customer\Command\LoginCustomerCommand;
use App\Application\Customer\DTO\LoginRequest;
use App\Application\Customer\DTO\AuthView;
use App\Application\Customer\Service\TokenServiceInterface;
use App\Infrastructure\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

final class LoginController extends AbstractController
{
    #[Route('/customers/login', name: 'login_customer', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] LoginRequest $request,
        CommandBus $commandBus,
        TokenServiceInterface $tokenService
    ): JsonResponse {
        $customer = $commandBus->dispatch(
            new LoginCustomerCommand($request->name, $request->password)
        );

        $token = $tokenService->createToken($customer);

        return $this->json(new AuthView($token), Response::HTTP_OK);
    }
}
