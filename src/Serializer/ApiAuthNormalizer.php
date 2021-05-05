<?php


namespace App\Serializer;


use App\Attribute\ApiAuthGroups;
use App\Entity\Post;
use App\Security\Voter\UserOwnedVoter;
use ArrayObject;
use ReflectionClass;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ApiAuthNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED_NORMALIZER = 'PostApiNormalizerAlreadyCalled';

    public function __construct(private AuthorizationCheckerInterface $checker)
    {
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        if (!is_object($data)) {
            return false;
        }
        $class = new ReflectionClass(get_class($data));
        $classAttributes = $class->getAttributes(ApiAuthGroups::class);
        $alreadyCalled = $context[self::ALREADY_CALLED_NORMALIZER] ?? false;
        return $alreadyCalled === false && !empty($classAttributes);
    }

    public function normalize($object, string $format = null, array $context = []): float|int|bool|ArrayObject|array|string|null
    {
        $class = new ReflectionClass(get_class($object));
        $apiAuthGroups = $class->getAttributes(ApiAuthGroups::class)[0]->newInstance();
        foreach ($apiAuthGroups->groups as $role => $groups) {
            if ($this->checker->isGranted($role, $object)) {
                $context['groups'] = array_merge($context['groups'] ?? [], $groups);
            }
        }

        $context[self::ALREADY_CALLED_NORMALIZER] = true;
        return $this->normalizer->normalize($object, $format, $context);
    }
}