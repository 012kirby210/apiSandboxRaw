<?php


namespace App\Tests\functionnal;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use App\Repository\UserRepository;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;


class CheeseListingRessourceTest extends ApiTestCase
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

  public function testAnAuthenticatedUserShouldBeAbleToCreateCheeseListing()
  {
    // the client will boot the kernel :
    $client = self::createClient();
    $this->createTheTestUser();
    $this->loginAsTheTestUser($client);
  }

  private function createTheTestUser()
  {
    $user = new User();
    $user->setEmail($this->testUserEmail);
    $user->setPassword($this->testUserPasswordEncoded);
    $user->setUsername($this->testUserName);
    $entityManager = self::$container->get('doctrine')->getManager();
    $entityManager->persist($user);
    $entityManager->flush();
  }

  private function loginAsTheTestUser($client)
  {
    // dont remake another kernel or the trait will move out what the last kernel did
    $client->request('POST','/login',[
      'headers' => [ 'accept' => ['application/json'],
        'Content-type' => ['application/json']],
      'json' => ['email' => $this->testUserEmail, 'password' => $this->testUserPassword]
    ]);
    $this->assertResponseStatusCodeSame(204);
  }
}
