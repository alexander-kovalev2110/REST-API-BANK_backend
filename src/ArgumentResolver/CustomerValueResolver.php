<?php

namespace App\ArgumentResolver;

use App\Entity\Customer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CustomerValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(private Security $security) {}

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        // Resolver should only work for arguments of type Customer.
        return $argument->getType() === Customer::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new AccessDeniedHttpException('User is not authenticated.');
        }

        if (!$user instanceof Customer) {
            throw new AccessDeniedHttpException('Authenticated user is not a customer.');
        }

        yield $user;
    }
}