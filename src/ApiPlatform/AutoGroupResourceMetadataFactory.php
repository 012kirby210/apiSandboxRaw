<?php


namespace App\ApiPlatform;


use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Resource\ResourceMetadata;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;

use App\Kernel;
use App\Serializer\AdminGroupsContextBuilder;
use Psr\Log\LoggerInterface;

class AutoGroupResourceMetadataFactory implements ResourceMetadataFactoryInterface
{

  private $decorated;
  /**
   * @var LoggerInterface
   */
  private $logger;


  public function __construct(ResourceMetadataFactoryInterface $decorated,LoggerInterface $logger)
  {
    $this->decorated = $decorated;


    $this->logger = $logger;
  }
  /**
   * @inheritDoc
   */
  public function create(string $resourceClass): ResourceMetadata
  {

    $resourceMetadata = $this->decorated->create($resourceClass);

    $itemOperations = $resourceMetadata->getItemOperations();
    $resourceMetadata = $resourceMetadata->withItemOperations(
      $this->updateContextOnOperations($itemOperations,$resourceMetadata->getShortName(),true));

    $collectionOperations = $resourceMetadata->getCollectionOperations();
    $resourceMetadata = $resourceMetadata->withCollectionOperations(
      $this->updateContextOnOperations($collectionOperations,$resourceMetadata->getShortName(),false));

    return $resourceMetadata;
  }

  private function updateContextOnOperations(array $operations, string $shortName, bool $isItem)
  {
    foreach ($operations as $operationName => $operationOptions){
      $operationOptions['normalization_context'] = $operationOptions['normalization_context'] ?? [];
      $operationOptions['normalization_context']['groups'] = $operationOptions['normalization_context']['groups'] ?? [];
      $operationOptions['normalization_context']['groups'] = array_unique(array_merge(
        $operationOptions['normalization_context']['groups'],
        $this->getDefaultGroups($shortName,true,$isItem,$operationName)
      ));

      $operationOptions['denormalization_context'] = $operationOptions['denormalization_context'] ?? [];
      $operationOptions['denormalization_context']['groups'] = $operationOptions['denormalization_context']['groups'] ?? [];
      $operationOptions['denormalization_context']['groups'] = array_unique(array_merge(
        $operationOptions['denormalization_context']['groups'],
        $this->getDefaultGroups($shortName,false,$isItem,$operationName)
      ));
      $operations[$operationName] = $operationOptions;
    }

    return $operations;
  }



  private function getDefaultGroups(string $shortName, bool $normalization,
                                    bool $isItem,
                                    string $operationName)
  {
    $resourceClass = $context['resource_class'] ?? null;

    $operationType = $normalization === true ? 'read' : 'write';
    $setType = $isItem ? 'item' : 'collection';

    $returnedValue = [
      sprintf('%s:%s', $shortName, $operationType),
      sprintf('%s:%s:%s', $shortName, $setType, $operationType),
      sprintf('%s:%s:%s', $shortName, $setType, $operationName)
    ];

    return $returnedValue;

  }
}
