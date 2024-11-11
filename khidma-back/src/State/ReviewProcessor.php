<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Review;
use Symfony\Bundle\SecurityBundle\Security;

class ReviewProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof Review && null === $data->getAuthor()) {
            $data->setAuthor($this->security->getUser());
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}