<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Service;
use Symfony\Bundle\SecurityBundle\Security;

class ServiceProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Service && null === $data->getProvider()) {
            $data->setProvider($this->security->getUser());
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}