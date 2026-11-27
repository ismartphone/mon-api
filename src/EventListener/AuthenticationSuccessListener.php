<?php

namespace App\EventListener;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Adds the token lifetime and the user's data to the login response.
 */
#[AsEventListener(event: Events::AUTHENTICATION_SUCCESS)]
final readonly class AuthenticationSuccessListener
{
    public function __construct(
        private NormalizerInterface $normalizer,
        #[Autowire(param: 'lexik_jwt_authentication.token_ttl')]
        private int $tokenTtl,
    ) {
    }

    public function __invoke(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            return;
        }

        $data = $event->getData();
        $data['ttl'] = $this->tokenTtl;
        $data['user'] = $this->normalizer->normalize($user, context: ['groups' => ['user:read']]);

        $event->setData($data);
    }
}
