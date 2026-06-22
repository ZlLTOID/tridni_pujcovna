<?php

namespace App\Security;

use App\Entity\Trida;
use App\Entity\Ucitel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/** @extends Voter<string, Trida> */
class TridaVoter extends Voter
{
    public const MANAGE = 'TRIDA_MANAGE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::MANAGE && $subject instanceof Trida;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof Ucitel) {
            return false;
        }

        if ($user->jeAdmin()) {
            return true;
        }

        return $subject->getUcitel()->getId() === $user->getId();
    }
}
