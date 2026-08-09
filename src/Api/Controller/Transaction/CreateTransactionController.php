<?php

namespace App\Api\Controller\Transaction;

use App\Domain\Customer\Customer;
use App\Application\Transaction\Command\CreateTransactionCommand;
use App\Application\Transaction\DTO\AmountTransactionRequest;
use App\Infrastructure\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CreateTransactionController extends AbstractController
{
    #[Route('/transactions', name: 'add_transaction', methods: ['POST'])]
    public function __invoke(
        AmountTransactionRequest $request,
        Customer $customer,
        CommandBus $commandBus
    ): JsonResponse {
        $result = $commandBus->dispatch(
            new CreateTransactionCommand($customer, $request->amount)
        );

        return $this->json($result, Response::HTTP_CREATED);
    }
}
