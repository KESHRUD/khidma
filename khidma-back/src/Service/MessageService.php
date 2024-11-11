<?php

namespace App\Service;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MessageService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HubInterface $hub
    ) {}

    public function sendMessage(User $sender, User $recipient, string $content): Message
    {
        $message = new Message();
        $message->setSender($sender)
                ->setRecipient($recipient)
                ->setContent($content);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Publier sur Mercure
        $update = new Update(
            [
                "chat/{$recipient->getId()}",
                "chat/{$sender->getId()}"
            ],
            json_encode([
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'sender' => [
                    'id' => $sender->getId(),
                    'name' => $sender->getFullName()
                ],
                'createdAt' => $message->getCreatedAt()->format('c')
            ])
        );

        $this->hub->publish($update);

        return $message;
    }

    public function markAsRead(Message $message): void
    {
        if (!$message->isRead()) {
            $message->setIsRead(true)
                   ->setReadAt(new \DateTimeImmutable());
            
            $this->entityManager->flush();

            // Notifier l'expéditeur
            $update = new Update(
                "chat/{$message->getSender()->getId()}",
                json_encode([
                    'type' => 'message_read',
                    'messageId' => $message->getId(),
                    'readAt' => $message->getReadAt()->format('c')
                ])
            );

            $this->hub->publish($update);
        }
    }

    public function getConversation(User $user1, User $user2, int $limit = 20, int $offset = 0): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('m')
            ->from(Message::class, 'm')
            ->where('(m.sender = :user1 AND m.recipient = :user2) OR (m.sender = :user2 AND m.recipient = :user1)')
            ->setParameters([
                'user1' => $user1,
                'user2' => $user2
            ])
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function getUnreadCount(User $user): int
    {
        return $this->entityManager->createQueryBuilder()
            ->select('COUNT(m.id)')
            ->from(Message::class, 'm')
            ->where('m.recipient = :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}