<?php

namespace App\Api\Controller\Transaction;

use App\Domain\Customer\Customer;
use App\Application\Transaction\Command\DeleteTransactionCommand;
use App\Infrastructure\Bus\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Annotation\Route;

final class DeleteTransactionController extends AbstractController
{
    #[Route('/transactions/{transactionId}', name: 'delete_transaction', methods: ['DELETE'])]
    public function __invoke(
        int $transactionId,
        #[CurrentUser] Customer $customer,
        CommandBus $commandBus
    ): JsonResponse {
        $result = $commandBus->dispatch(
            new DeleteTransactionCommand($customer, $transactionId)
        );

        return $this->json($result, Response::HTTP_OK);
    }
}
