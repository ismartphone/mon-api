<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Validates an entity and turns violations into a 422 response,
 * using the same format as #[MapRequestPayload] errors.
 */
final readonly class EntityValidator
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function validate(object $entity): void
    {
        $violations = $this->validator->validate($entity);

        if (\count($violations) > 0) {
            throw new UnprocessableEntityHttpException('Validation failed.', new ValidationFailedException($entity, $violations));
        }
    }
}
