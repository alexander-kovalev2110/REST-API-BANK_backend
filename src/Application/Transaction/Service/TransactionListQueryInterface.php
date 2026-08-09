<?php

namespace App\Application\Transaction\Service;

use App\Application\Transaction\Query\GetTransactionListQuery;
use App\Application\Transaction\DTO\TransactionListView;

interface TransactionListQueryInterface
{
    public function getList(GetTransactionListQuery $query): TransactionListView;
}
