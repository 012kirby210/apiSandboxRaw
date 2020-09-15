<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Entity\User;

class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

  /**
   * That normalizer will only be called on user type of object due to supports function.
   * @param User $object
   * @param string|null $format
   * @param array $context
   * @return array
   * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
   */
    public function normalize($object, $format = null, array $context = array()): array
    {
        if ($this->userIsOwner($object)){
          isset($context['groups']) && $context['groups'][] = 'owner:read';
        }

      $data = $this->normalizer->normalize($object, $format, $context);

        // Here: add, edit, or delete some data

        return $data;
    }

  /**
   * Will that normalizer enter the normization chain for the what kind of object ?
   * @param mixed $data
   * @param string | null $format
   * @return bool
   */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    private function userIsOwner(User $user): bool
    {
      $random = mt_rand(0,10);
      $returnedValue = $random > 5;
      error_log("random value {$random}, returned Value : {$returnedValue}" );

      return $returnedValue;
    }
}
