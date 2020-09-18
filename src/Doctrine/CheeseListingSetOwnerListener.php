<?php


namespace App\Doctrine;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use App\Entity\CheeseListing;

class CheeseListingSetOwnerListener
{
	/**
	 * @var Security $security
	 */
	private $security;

	public function __construct(Security $security){

		$this->security = $security;
	}

	public function prePersist(CheeseListing $cheeseListing)
	{
		if ($cheeseListing->getOwner() === null){
			/** @var User $user */
			$user = $this->security->getUser();
			$cheeseListing->setOwner($user);
		}
	}
}
