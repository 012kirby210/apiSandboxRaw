<?php


namespace App\ApiPlatform;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\CheeseListing;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use \ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use Symfony\Component\Security\Core\Security;

class CheeseListingIsPublishedExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
	/**
	 * @var Security
	 */
	private $security;

	public function __construct(Security $security)
	{

		$this->security = $security;
	}

	public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
	{
		$this->addWhere($resourceClass, $queryBuilder);
	}

	public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
	{
		$this->addWhere($resourceClass, $queryBuilder);
	}

	/**
	 * @param string $resourceClass
	 * @param QueryBuilder $queryBuilder
	 */
	public function addWhere(string $resourceClass, QueryBuilder $queryBuilder) :void
	{
		if ($resourceClass !== CheeseListing::class) {
			return;
		}

		if ($this->security->isGranted('ROLE_ADMIN')) {
			return;
		}

		$rootAliases = $queryBuilder->getRootAliases();
		$rootAlias = $rootAliases[0];
		$queryBuilder->andWhere(sprintf('%s.isPublished = :isPublished', $rootAlias))->setParameter('isPublished', true);
	}
}
