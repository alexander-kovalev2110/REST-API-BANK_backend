<?php

namespace App\Api\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class RequestPayloadResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        $type = $argument->getType();
        if ($type === null) {
            return false;
        }

        return str_ends_with($type, 'Request') && !str_ends_with($type, 'FilterTransactionRequest');
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();
        $content = $request->getContent();

        if (empty($content)) {
            throw new BadRequestHttpException('Request body cannot be empty.');
        }

        try {
            $dto = $this->serializer->deserialize($content, $type, 'json');
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Invalid JSON payload: ' . $e->getMessage());
        }

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
