<?php


namespace App\Serializer;


use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PostContextBuilder implements SerializerContextBuilderInterface
{

    public function __construct(private SerializerContextBuilderInterface $decorated, private AuthorizationCheckerInterface $checker)
    {
    }

    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;
        if (
            $resourceClass === Post::class &&
            $context['groups'] &&
            $this->checker->isGranted('ROLE_USER')
        ) {
            $context['groups'][] = 'read:collection:User';
        }
        return $context;
    }
}