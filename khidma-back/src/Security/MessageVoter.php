<?php

namespace App\Security\Voter;

use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class MessageVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, ['VIEW_MESSAGE', 'DELETE_MESSAGE'])
            && $subject instanceof Message;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Message $message */
        $message = $subject;

        return match($attribute) {
            'VIEW_MESSAGE' => $this->canView($message, $user),
            'DELETE_MESSAGE' => $this->canDelete($message, $user),
            default => false,
        };
    }

    private function canView(Message $message, User $user): bool
    {
        return $message->getSender() === $user || $message->getRecipient() === $user;
    }

    private function canDelete(Message $message, User $user): bool
    {
        return $message->getSender() === $user && 
               $message->getCreatedAt()->modify('+24 hours') > new \DateTimeImmutable();
    }
}