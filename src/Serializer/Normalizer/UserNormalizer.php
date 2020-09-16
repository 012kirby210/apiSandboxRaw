<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Entity\User;

class UserNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'USER_NORMALIZER_ALREADY_CALLED';

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
          if (isset($context['groups'])){
						$context['groups'][] = 'owner:read';
						$context[self::ALREADY_CALLED] = true;
          }

        }

      $data = $this->normalizer->normalize($object, $format, $context);

        // Here: add, edit, or delete some data

        return $data;
    }

  /**
   * Will that normalizer enter the normization chain for the what kind of object ?
   * @param mixed $data
   * @param string | null $format
	 * @param array $context
   * @return bool
   */
	public function supportsNormalization($data, string $format = null, array $context = [])
	{
		$alreadyCalled = isset($context[self::ALREADY_CALLED]) ? $context[self::ALREADY_CALLED] : false;
		return ($alreadyCalled) && ($data instanceof User);
	}


    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

    private function userIsOwner(User $user): bool
    {
      $random = mt_rand(0,10);
      $returnedValue = $random > 5;
      error_log("random value {$random}, returned Value : {$returnedValue}" );

      return $returnedValue;
    }


}
