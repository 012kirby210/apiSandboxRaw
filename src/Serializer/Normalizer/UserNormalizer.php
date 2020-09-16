<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Security\Core\Security;
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
		private $security;

    public function __construct(Security $security)
		{
			$this->security = $security;
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
    	$isOwner = $this->userIsOwner($object);

        if ($isOwner){
          if (isset($context['groups'])){
						$context['groups'][] = 'owner:read';
          }
        }
        // the process of normalization will occur on each User normalized
				$context[self::ALREADY_CALLED] = true;

      $data = $this->normalizer->normalize($object, $format, $context);
			$data['isMe'] = $isOwner;
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
		return ($data instanceof User) && !$alreadyCalled;
	}


    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

    private function userIsOwner(User $user): bool
    {
    	/** @var User $authenticatedUser */
    	$authenticatedUser = $this->security->getUser();
    	$returnedValue = false;
    	if ($authenticatedUser !== null)
			{
				$returnedValue = $authenticatedUser->getEmail() === $user->getEmail();
			}
    	return $returnedValue;
    }


}
