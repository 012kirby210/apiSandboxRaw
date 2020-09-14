<?php


namespace App\Serializer;


use ApiPlatform\Core\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use \ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\User;

class AdminGroupsContextBuilder implements SerializerContextBuilderInterface
{
  const OPERATION_TYPE_READ = 'read';
  const OPERATION_TYPE_WRITE = 'write';

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

    $context['groups'] = $context['groups'] ?? [];
    $context['groups'] = array_merge($context['groups'],$this->addDefaultGroups($context,$normalization));

    $resourceClass = $context['resource_class'] ?? null;

    // add the admin:api_read/write group to the normalization/denormalizatin context for the admin users
    if ( $resourceClass === User::class && $this->authorizationChecker->isGranted('ROLE_ADMIN')){
      $context['groups'][] = $normalization ? 'admin:read' : 'admin:write';
    }

    $context['groups'] = array_unique($context['groups']);

    return $context;
  }

  private function addDefaultGroups(array $context, bool $normalization)
  {
    $resourceClass = $context['resource_class'] ?? null;

    if ($resourceClass !== null){
      $shortName = (new \ReflectionClass($resourceClass))->getShortName();
      $classAlias = strtolower(preg_replace('/[A-Z]/','_\\0',lcfirst($shortName)));
      $operationType = $normalization ? self::OPERATION_TYPE_READ : self::OPERATION_TYPE_WRITE;
      $setType = isset($context['operation_type']) ? $context['operation_type'] : null;
      $operationName = null;
      if ($setType === 'item'){
        isset($context['item_operation_name']) && $operationName = $context['item_operation_name'];
      }else{
        isset($context['collection_operation_name']) && $operationName = $context['collection_operation_name'];
      }

      return [
        sprintf('%s:%s', $classAlias, $operationType),
        sprintf('%s:%s:%s', $classAlias, $setType, $operationType),
        sprintf('%s:%s:%s', $classAlias, $setType, $operationName)
      ];
    }
  }
}
