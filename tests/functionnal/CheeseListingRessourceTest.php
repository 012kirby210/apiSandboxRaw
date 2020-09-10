<?php


namespace App\Tests\functionnal;

use Doctrine\ORM\EntityManager;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\CheeseListing;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use App\Tests\BaseClasses\UserFriendlyTestCase;


class CheeseListingRessourceTest extends UserFriendlyTestCase
{

  use ReloadDatabaseTrait;

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

}
