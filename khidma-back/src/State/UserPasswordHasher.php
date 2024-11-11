<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Psr\Log\LoggerInterface;

final class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $processor,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly LoggerInterface $logger
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User || !$data->getPlainPassword()) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        try {
            $hashedPassword = $this->passwordHasher->hashPassword(
                $data,
                $data->getPlainPassword()
            );
            
            $data->setPassword($hashedPassword);
            $data->eraseCredentials();

            $this->logger->info('Password hashed successfully for user', [
                'email' => $data->getEmail()
            ]);

            return $this->processor->process($data, $operation, $uriVariables, $context);
        } catch (\Exception $e) {
            $this->logger->error('Error hashing password', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}