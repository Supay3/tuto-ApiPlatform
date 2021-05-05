<?php


namespace App\Serializer;


use App\Entity\Post;
use ArrayObject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Vich\UploaderBundle\Storage\StorageInterface;

class PostNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{

    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'PostNormalizerAlreadyCalled';

    public function __construct(private StorageInterface $storage)
    {
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return !isset($context[self::ALREADY_CALLED]) && $data instanceof Post;
    }

    /**
     * @param Post $object
     * @param string|null $format
     * @param array $context
     * @return array|ArrayObject|bool|float|int|string|null
     * @throws ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = []): float|array|bool|ArrayObject|int|string|null
    {
        $object->setFileUrl($this->storage->resolveUri($object, 'file'));
        $context[self::ALREADY_CALLED] = true;
        return $this->normalizer->normalize($object, $format, $context);
    }
}