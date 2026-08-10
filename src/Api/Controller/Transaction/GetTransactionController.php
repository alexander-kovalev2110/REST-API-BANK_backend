<?php

namespace App\Api\Controller\Transaction;

use App\Domain\Customer\Customer;
use App\Application\Transaction\Query\GetTransactionQuery;
use App\Infrastructure\Bus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Annotation\Route;

final class GetTransactionController extends AbstractController
{
    #[Route('/transactions/{transactionId}', name: 'get_transaction', methods: ['GET'])]
    public function __invoke(
        int $transactionId,
        #[CurrentUser] Customer $customer,
        QueryBus $queryBus
    ): JsonResponse {
        $result = $queryBus->ask(
            new GetTransactionQuery($customer, $transactionId)
        );

        return $this->json($result, Response::HTTP_OK);
    }
}
