<?php


namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserDataPersister implements DataPersisterInterface
{

  private $em;
  private $passwordEncoder;
  private $kernel;

  public function __construct(EntityManagerInterface $entityManager,UserPasswordEncoderInterface $encoder,
                              Kernel $kernel)
  {
    $this->em = $entityManager;
    $this->passwordEncoder = $encoder;
    $this->kernel = $kernel;
  }


  /**
   * @inheritDoc
   */
  public function supports($data): bool
  {
    return $data instanceof User;
  }

  /**
   * @inheritDoc
   */
  public function persist($data)
  {
    /** @var User $user */
    $user = $data;
    $userPlainPassword = $user->getPlainPassword();

    if ($userPlainPassword && $userPlainPassword !== ''){
      $user->setPassword(
        $this->passwordEncoder->encodePassword($user,$userPlainPassword));
      $user->eraseCredentials();
    }
    $this->em->persist($user);
    $this->em->flush();
  }

  /**
   * @inheritDoc
   */
  public function remove($data)
  {
    $this->em->remove($data);
    $this->em->flush();
  }
}
