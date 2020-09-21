<?php


namespace App\Tests\functionnal;

use Doctrine\ORM\EntityManager;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\CheeseListing;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use App\Tests\BaseClasses\UserFriendlyTestCase;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\ResponseInterface;


class CheeseListingRessourceTest extends UserFriendlyTestCase
{

  use ReloadDatabaseTrait;

  private $goodHeaders = [ 'accept' => ['application/json'],
          'Content-type' => ['application/json']];

  private $testUserEmail = 'testUser1@mail.com';
  private $testUserPasswordEncoded = '$argon2id$v=19$m=65536,t=4,p=1$YSHoMb8usYjsF6cjNB+HzA$juuLpWzuEjty1cuvvkMwEEpFKffSan75Cgh04/R5t54';
  private $testUserPassword = 'azerty';
  private $testUserName = 'testUser1';

  public function testAWrongApplicationTypeShouldNotBeAbleToAccessCreateCheeseListingEndpoint()
  {
    $client = self::createClient();
    $client->request('POST','/api/cheese_listings',[
      'headers' => [ 'accept' => [ 'application/x-www-form-urlencoded' ]],
      'body' => ['a' => 'a', 'b' => 'b']
    ]);
    $this->assertResponseStatusCodeSame(406);
  }

  public function testAUnauthenticatedUserShouldNotBeAbleToCheeseListing()
  {
    $client = self::createClient();
    $client->request('POST','/api/cheese_listings',[
      'headers' => [ 'accept' => [ 'application/json' ]],
      'json' => []
    ]);
    $this->assertResponseStatusCodeSame(401);
  }

  public function testAnAuthenticatedUserShouldBeAbleToCheeseListing()
  {
    $client = self::createClient();
    $this->createUser($client,'testuser@mail.com','azerty');
    $this->loginAsUser($client,'testuser@mail.com','azerty');
  }

  public function testAnOwnerOfACheeseListingShouldBeAbleToUpdateACheeselisting()
  {
    $client = self::createClient();
    $user = $this->createUser($client,'testuser@mail.com','azerty');

    $cheeseListing = new CheeseListing();
    $cheeseListing->setOwner($user);
    $cheeseListing->setTitle('A block of cheddar');
    $cheeseListing->setDescription('awesome');
    $cheeseListing->setPrice(1000);
    $entityManager = self::$container->get('doctrine.orm.default_entity_manager');
    $entityManager->persist($cheeseListing);
    $entityManager->flush();

    $this->loginAsUser($client,'testuser@mail.com','azerty');
    $client->request('PUT','/api/cheese_listings/'.$cheeseListing->getId(),
    [
      'headers' => [ 'accept' => ['application/json'],
        'Content-type' => ['application/json']],
      'json' => [
        'title' => 'updated'
      ]
    ]);
    $this->assertResponseStatusCodeSame(200);
  }

  public function testNoOwnerShouldBeAbleToUpdateACheeseListing()
  {
    $client = self::createClient();
    $user = $this->createUser($client,'testuser@mail.com','azerty');
    $userTrying = $this->createUser($client,'testusertrying@mail.com','azerty');

    $cheeseListing = new CheeseListing();
    $cheeseListing->setOwner($user);
    $cheeseListing->setTitle('A block of cheddar');
    $cheeseListing->setDescription('awesome');
    $cheeseListing->setPrice(1000);
    $entityManager = self::$container->get('doctrine.orm.default_entity_manager');
    $entityManager->persist($cheeseListing);
    $entityManager->flush();

    $this->loginAsUser($client,'testusertrying@mail.com','azerty');
    $client->request('PUT','/api/cheese_listings/'.$cheeseListing->getId(),
      [
        'headers' => [ 'accept' => ['application/json'],
          'Content-type' => ['application/json']],
        'json' => [
          'title' => 'updated'
        ]
      ]);
    $this->assertResponseStatusCodeSame(403);
  }

  public function testNoOwnerShouldChangeTheOwnerOfACheeseListingButIts()
  {
    $client = self::createClient();
    $user = $this->createUser($client,'testuser@mail.com','azerty');
    $userTrying = $this->createUser($client,'testusertrying@mail.com','azerty');

    $cheeseListing = new CheeseListing();
    $cheeseListing->setOwner($user);
    $cheeseListing->setTitle('A block of cheddar');
    $cheeseListing->setDescription('awesome');
    $cheeseListing->setPrice(1000);
    $entityManager = self::$container->get('doctrine.orm.default_entity_manager');
    $entityManager->persist($cheeseListing);
    $entityManager->flush();

    $this->loginAsUser($client,'testusertrying@mail.com','azerty');
    $client->request('PUT','/api/cheese_listings/'.$cheeseListing->getId(),
      [
        'headers' => [ 'accept' => ['application/json'],
          'Content-type' => ['application/json']],
        'json' => [
          'title' => 'updated',
          'owner' => '/api/users/'.$userTrying->getId()
        ]
      ]);
    $this->assertResponseStatusCodeSame(403);
  }

  public function testAnyOwnerOfACheeseListingShouldBeAbleToGiveOwnershipToAnotherUser()
  {
    $client = self::createClient();
    $firstOwner = $this->createUser($client,'firstowner@mail.com','azerty');
    $newOwner = $this->createUser($client,'newOwner@mail.com','azerty');

    $cheeseListing = new CheeseListing();
    $cheeseListing->setOwner($firstOwner);
    $cheeseListing->setTitle('A block of cheddar');
    $cheeseListing->setDescription('awesome');
    $cheeseListing->setPrice(1000);
    $entityManager = self::$container->get('doctrine.orm.default_entity_manager');
    $entityManager->persist($cheeseListing);
    $entityManager->flush();

    $this->loginAsUser($client,'firstowner@mail.com','azerty');
    // change ownership

		// validation hanging there
		// it will try to match authenticated with validating value,
		// It should try to match authenticated with already recorded one.
		// so the way to do it is to extend the security voter on the post collection operation
		// and making it happens on security_post_denormalize instead of security
		// go check CheeseListing collection operation annotation

    $responseInterface = $client->request('PUT','/api/cheese_listings/'.$cheeseListing->getId(),
      [
        'headers' => [ 'accept' => ['application/json'],
          'Content-type' => ['application/json']],
        'json' => [
          'title' => 'updated',
          'owner' => '/api/users/'.$newOwner->getId()
        ]
      ]);

    $this->assertResponseStatusCodeSame(200);
    // try to re-access

    $client->request('PUT','/api/cheese_listings/'.$cheeseListing->getId(),
      [
        'headers' => [ 'accept' => ['application/json'],
          'Content-type' => ['application/json']],
        'json' => [
          'title' => 'updated',
          'owner' => '/api/users/'.$newOwner->getId()
        ]
      ]);
    $this->assertResponseStatusCodeSame(403);

    $this->loginAsUser($client,'newOwner@mail.com','azerty');
    $client->request('PUT','/api/cheese_listings/'.$cheeseListing->getId(),
      [
        'headers' => [ 'accept' => ['application/json'],
          'Content-type' => ['application/json']],
        'json' => [
          'title' => 'changed ownership title'
        ]
      ]);
    $this->assertResponseStatusCodeSame(200);
  }

  public function testAnAdminUserCanAttributeCheeselistinToAnotherOwner()
	{
		$client = self::createClient();
		$admin = $this->createUser($client,'admin@mail.com','password',['ROLE_ADMIN']);
		$this->loginAsUser($client,'admin@mail.com','password');
		$anyUser = $this->createUser($client,'anyUser@mail.com','password');
		$cheeseListingData = [
			'title' => 'titre de l\'article',
			'price' => 1560,
			'isPublished' => true,
			'owner' => '/api/users/' . $anyUser->getId(),
			'description' => 'je suis une description'
		];
		/** @var ResponseInterface $response */
		$response = $client->request('POST','/api/cheese_listings',
		[
			'headers' => $this->goodHeaders,
			'json' => $cheeseListingData
		]);
		$this->assertResponseStatusCodeSame(201);
		$data = $response->toArray(true);
		$cheeseListingId = null;
		try{
			$cheeseListingId = $data['id'];
		}catch(TransportException $e){
			error_log($e->getMessage());
		}catch(JsonException $e){
			error_log($e->getMessage());
		}


		// auto fail on no returned id
		$cheeseListingId === null && $this->assertTrue(false);

		// we gonna to test put also.
		$yetAnotherUser = $this->createUser($client,'yetAnotherUser@mail.com','password');
		$cheeseListingData['owner'] = '/api/users/' . $yetAnotherUser->getId();
		$client->request('PUT','/api/cheese_listings/' . $cheeseListingId,
		[
			'headers' => $this->goodHeaders,
			'json' => $cheeseListingData
		]);
		$this->assertResponseIsSuccessful();
	}

	public function testAnAuthenticatedUserCannotAttributeOwnershipOnCheeselistingCreationButIts()
	{
		$client = self::createClient();
		$authenticatedUser = $this->createUser($client,'authenticatedUser@mail.com','password');
		$otherUser = $this->createUser($client,'otherUser@mail.com','password');
		$this->loginAsUser($client,'authenticatedUser@mail.com','password');
		$cheeseListingData = [
			'title' => 'titre de l\'article',
			'price' => 1560,
			'isPublished' => true,
			'owner' => '/api/users/' . $otherUser->getId(),
			'description' => 'je suis une description'
		];
		$client->request('POST','/api/cheese_listings',
		[
			'headers' => $this->goodHeaders,
			'json' => $cheeseListingData
		]);
		$this->assertResponseStatusCodeSame(403);
	}

	public function testAnAuthenticatedUserShouldBeAbleToCreateCheeseListingWithoutSpecifyingAnOwner()
	{
		$client = self::createClient();
		$this->createUser($client,'user@mail.com','password');
		$this->loginAsUser($client,'user@mail.com','password');
		$cheeseListingData = [
			'title' => 'titre de l\'article',
			'price' => 1560,
			'isPublished' => true,
			'description' => 'je suis une description'
		];

		$responseInterface = $client->request('POST','/api/cheese_listings',
		[
			'headers' => $this->goodHeaders,
			'json' => $cheeseListingData
		]);
		$this->assertResponseStatusCodeSame(201);
		try{
			$arrayData = $responseInterface->toArray();
			$this->assertArrayHasKey('owner',$arrayData);
			$ownerIri = $arrayData['owner'];
			$this->assertTrue($ownerIri !== 'null');
		}catch(\Exception $e){
			$e->getMessage();
		}
	}

	public function testCheeseListingsShouldBeListedDependingOnItsActiveStatus()
	{
		$client = self::createClient();
		$user = $this->createUser($client, 'user@mail.com', 'password');
		$this->loginAsUser($client, 'user@mail.com','password');
		$cheeseListing1 = new CheeseListing('cheese1');
		$cheeseListing1->setOwner($user);
		$cheeseListing1->setPrice(1000);
		$cheeseListing1->setDescription('cheese');
		$cheeseListing2 = new CheeseListing('cheese2');
		$cheeseListing2->setOwner($user);
		$cheeseListing2->setPrice(1000);
		$cheeseListing2->setDescription('cheese');
		$cheeseListing2->setIsPublished(true);
		$cheeseListing3 = new CheeseListing('cheese3');
		$cheeseListing3->setOwner($user);
		$cheeseListing3->setPrice(1000);
		$cheeseListing3->setDescription('cheese');
		$cheeseListing3->setIsPublished(true);
		$em = self::$container->get('doctrine.orm.entity_manager');
		$em->persist($cheeseListing1);
		$em->persist($cheeseListing2);
		$em->persist($cheeseListing3);
		$em->flush();

		$responseInterface = $client->request('GET','/api/cheese_listings');
		$this->assertJsonContains(['hydra:totalItems' => 2]);
	}

	public function testUnpublishedCheeseListingShouldLandOn404()
	{
		$client = self::createClient();
		$user = $this->createUser($client,'user@mail.com','password');
		$otherUser = $this->createUser($client,'other@mail.com','password');
		$cheeseListing1 = new CheeseListing('cheese1');
		$cheeseListing1->setOwner($user);
		$cheeseListing1->setPrice(1000);
		$cheeseListing1->setDescription('cheese');
		$cheeseListing1->setIsPublished(false);
		$em = self::$container->get('doctrine.orm.entity_manager');
		$em->persist($cheeseListing1);
		$em->flush();
		$this->loginAsUser($client,'other@mail.com','password');
		$client->request('GET','/api/cheese_listings/'.$cheeseListing1->getId());
		$this->assertResponseStatusCodeSame(404);
		$responseInteface = $client->request('GET','/api/users/'.$user->getId());
		$dataArray = $responseInteface->toArray();
		var_dump($dataArray);
	}

}
