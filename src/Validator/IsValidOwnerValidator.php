<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidOwnerValidator extends ConstraintValidator
{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security){

		$this->security = $security;
	}

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint \App\Validator\IsValidOwner */
				$requestingUser = $this->security->getUser();

				if (!($requestingUser instanceof User)){
					$this->context->buildViolation($constraint->AnonymousMessage)
						->addViolation();
					return;
				}

				if (null === $value || '' === $value) {
					return;
				}

				if (!($value instanceof User)){
					throw new \InvalidArgumentException("The @ValidOwner must be set on a User type property.");
				}

				if ($this->security->isGranted('ROLE_ADMIN')){
					return;
				}

  			/** @var User value */
        /*if ($value->getId() !== $requestingUser->getId()){
					$this->context->buildViolation($constraint->message)
						->addViolation();
				}*/

    }
}
