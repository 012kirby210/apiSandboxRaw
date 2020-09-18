<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Define if the owner of listing can be validated
 * @Annotation
 */
class IsValidOwner extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = 'Cannot change the value of owner.';
		public $AnonymousMessage = 'Only authenticated users can set ownership.';
}
