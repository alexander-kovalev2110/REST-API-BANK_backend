<?php

namespace App\Api\Controller\Transaction;

use App\Domain\Customer\Customer;
use App\Application\Transaction\Query\GetTransactionListQuery;
use App\Application\Transaction\DTO\FilterTransactionRequest;
use App\Infrastructure\Bus\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Annotation\Route;

final class GetTransactionListController extends AbstractController
{
    #[Route('/transactions', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] ?FilterTransactionRequest $dto = null,
        #[CurrentUser] Customer $customer,
        QueryBus $queryBus
    ): JsonResponse {
        $dto = $dto ?? new FilterTransactionRequest(null, null, 1, 10);
        $query = new GetTransactionListQuery(
            customer: $customer,
            amount: $dto->amount,
            date: $dto->date,
            page: $dto->page,
            limit: $dto->limit
        );

        $result = $queryBus->ask($query);

        return $this->json($result);
    }
}
