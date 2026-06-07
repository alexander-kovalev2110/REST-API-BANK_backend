<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidatorService
{
    public function __construct(private readonly ValidatorInterface $validator) {}

    public function validate(object $dto): void
    {
        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }

            throw new BadRequestHttpException(json_encode($messages, JSON_UNESCAPED_UNICODE));
        }
    }
}
