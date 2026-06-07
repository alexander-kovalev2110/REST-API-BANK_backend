<?php

namespace App\ArgumentResolver;

use App\DTO\FilterTransactionRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class FilterTransactionRequestResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly ValidatorInterface $validator
    ) {}

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === FilterTransactionRequest::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $dateString = $request->query->get('date');
        $date = null;

        if ($dateString !== null) {
            try {
                $date = new \DateTimeImmutable($dateString);
            } catch (\Exception) {
                throw new BadRequestHttpException(
                    json_encode([
                        'message' => 'Validation failed',
                        'errors' => [
                            'date' => 'Invalid date format. Expected Y-m-d.'
                        ]
                    ])
                );
            }
        }

        $dto = new FilterTransactionRequest(
            date: $date,
            amount: $request->query->get('amount') !== null
                ? (float) $request->query->get('amount')
                : null,
            page: (int) $request->query->get('page', 1),
            limit: (int) $request->query->get('limit', 10),
        );

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $formatted = [];

            foreach ($errors as $error) {
                $formatted[$error->getPropertyPath()] = $error->getMessage();
            }

            throw new BadRequestHttpException(
                json_encode([
                    'message' => 'Validation failed',
                    'errors' => $formatted,
                ], JSON_UNESCAPED_UNICODE)
            );
        }

        yield $dto;
    }
}
