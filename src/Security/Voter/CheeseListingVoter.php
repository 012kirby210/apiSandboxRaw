<?php

namespace App\Security\Voter;

use App\Entity\CheeseListing;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CheeseListingVoter extends Voter
{
  const EDIT_PERMISSION = 'EDIT';

  private $security;

  public function __construct(Security $security)
  {
    $this->security = $security;
  }

  protected function supports($attribute, $subject)
  {
    // replace with your own logic
    // https://symfony.com/doc/current/security/voters.html
    return in_array($attribute, [self::EDIT_PERMISSION])
      && $subject instanceof \App\Entity\CheeseListing;
  }

  protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
  {
    $isAllowed = false;
    $user = $token->getUser();
    // if the user is anonymous, do not grant access
    if (!$user instanceof UserInterface) {
      return false;
    }

    /** @var CheeseListing $cheeseListing */
    $cheeseListing = $subject;
    // ... (check conditions and return true to grant permission) ...
    switch ($attribute) {
      case self::EDIT_PERMISSION:
        // logic to determine if the user can EDIT
        // return true or false
        $isAllowed = $this->evaluateEditPermission($user,$cheeseListing);
        break;
      case 'POST_VIEW':
        // logic to determine if the user can VIEW
        // return true or false
        break;
      default:
        break;
    }

    return $isAllowed;
  }

  private function evaluateEditPermission(User $user, CheeseListing $cheeseListing)
  {
    return ($this->security->isGranted('ROLE_ADMIN')
      || $cheeseListing->getOwner() === $user);
  }
}
