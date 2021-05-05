<?php


namespace App\Serializer;


use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class FileDenormalizer implements DenormalizerInterface
{

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $data;
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $data instanceof File;
    }
}