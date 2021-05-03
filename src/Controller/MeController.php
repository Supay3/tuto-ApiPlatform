<?php


namespace App\Controller;


use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class MeController
{
    public function __construct(private Security $security)
    {
    }

    public function __invoke(): ?UserInterface
    {
        return $this->security->getUser();
    }
}