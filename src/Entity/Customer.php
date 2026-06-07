<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: "customers")]
class Customer implements UserInterface, PasswordAuthenticatedUserInterface
// class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $name;

    #[ORM\Column(name: 'pw', type: 'string', length: 255)]
    private $pw;

    public function getId(): ?int
    {
        return $this->id;
    }

    // 🔹 добавим alias для удобства, чтобы код с `$this->getUser()->getCustomerId()` не ломался
    public function getCustomerId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->pw;
    }

    public function setPassword(string $password): self
    {
        $this->pw = $password;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->name;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
        // если храните plainPassword — обнуляйте его здесь
    }
}
