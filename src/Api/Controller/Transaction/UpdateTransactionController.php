<?php

namespace App\Api\Controller\Transaction;

use App\Domain\Customer\Customer;
use App\Application\Transaction\Command\UpdateTransactionCommand;
use App\Application\Transaction\DTO\AmountTransactionRequest;
use App\Infrastructure\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class UpdateTransactionController extends AbstractController
{
    #[Route('/transactions/{transactionId}', name: 'update_transaction', methods: ['PATCH'])]
    public function __invoke(
        int $transactionId,
        AmountTransactionRequest $request,
        Customer $customer,
        CommandBus $commandBus
    ): JsonResponse {
        $result = $commandBus->dispatch(
            new UpdateTransactionCommand($customer, $transactionId, $request->amount)
        );

        return $this->json($result, Response::HTTP_OK);
    }
}
