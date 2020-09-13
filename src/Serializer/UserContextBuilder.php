<?php


namespace App\Serializer;


use ApiPlatform\Core\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use \ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\User;

class UserContextBuilder implements SerializerContextBuilderInterface
{

  private $decorated;
  private $authorizationChecker;


  public function __construct(SerializerContextBuilderInterface $decorated,
                              AuthorizationCheckerInterface $authorizationChecker)
  {
    $this->decorated = $decorated;
    $this->authorizationChecker = $authorizationChecker;
  }

  /**
   * @inheritDoc
   */
  public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
  {
    // gather the inner decorated service's context
    $context = $this->decorated->createFromRequest($request,$normalization,$extractedAttributes);
    $resourceClass = $context['resource_class'] ?? null;

    // add the admin:api_read/write group to the normalization/denormalizatin context for the admin users
    if ( $resourceClass === User::class && isset($context['groups']) &&
      $this->authorizationChecker->isGranted('ROLE_ADMIN')){
      $context['groups'][] = $normalization ? 'admin:api_read' : 'admin:api_write';
    }

    return $context;
  }
}
