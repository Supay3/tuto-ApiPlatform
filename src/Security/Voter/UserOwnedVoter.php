<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\UserOwnedInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserOwnedVoter extends Voter
{

    public const CAN_EDIT = 'CAN_EDIT';

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::CAN_EDIT])
            && $subject instanceof UserOwnedInterface;
    }

    /**
     * @param UserOwnedInterface $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $owner = $subject->getUser();

        switch ($attribute) {
            case self::CAN_EDIT:
                return $owner && $owner->getId() === $user->getId();
        }

        return false;
    }
}
