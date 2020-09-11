<?php


namespace App\Tests\functionnal;

use App\Tests\BaseClasses\UserFriendlyTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserRessourceTest extends UserFriendlyTestCase
{

  use ReloadDatabaseTrait;

  public $goodHeaders = [ 'accept' => ['application/json'],
    'content-type' => ['application/json']];

  public function testAnUnauthenticatedUserShouldNotBeAbleToAccessAnyUserInformation()
  {
    $client = self::createClient();
    $user = $this->createUser($client,'testuser@mail.com','password');

    $client->request('GET','/api/users/' . $user->getId(),
    [
      'headers' => [
        'accept' => [ 'application/json'],
        'content-type' => ['application/json']
      ],
    ]);
    $this->assertResponseStatusCodeSame(401);

    $client->request('PUT','/api/users/' . $user->getId(),
      [
        'headers' =>
          ['accept' => ['application/json'],
            'content-type' => ['application/json']
        ],
        'json' => [ 'username' => 'autreUsername']
      ]
    );
    $this->assertResponseStatusCodeSame(401);
  }

  public function testAnAuthorizedUserShouldBeAbleToAccessUserInformation()
  {
    $client = self::createClient();
    $user = $this->createUser($client,'testuser@mail.com','password');
    $this->loginAsUser($client,$user->getEmail(),'password');

    $client->request('GET','/api/users/' . $user->getId(),
      [
        'headers' => [
          'accept' => [ 'application/json'],
          'content-type' => ['application/json']
        ],
      ]);
    $this->assertResponseStatusCodeSame(200);
  }

  public function testAnAuthorizedUserShouldNotBeAbleToUpdateAnotherUserProfile()
  {
    $client = self::createClient();
    $user1 = $this->createUser($client,'testuser@mail.com','password');
    $this->loginAsUser($client,$user1->getEmail(),'password');
    $user2 = $this->createUser($client,'testuser2@mail.com','password');

    $client->request('PUT','/api/users/' . $user2->getId(),
      [
        'headers' =>
          ['accept' => ['application/json'],
            'content-type' => ['application/json']
          ],
        'json' => [ 'username' => 'autreUsername']
      ]
    );
    $this->assertResponseStatusCodeSame(403);
  }

  public function testAnAuthorizedUserShouldBeAbleToModifyItsOwnProfile()
  {
    $client = self::createClient();
    $user = $this->createUser($client,'testuser@mail.com','password');
    $this->loginAsUser($client,$user->getEmail(),'password');
    $client->request('PUT','/api/users/' . $user->getId(),
      [
        'headers' =>
          ['accept' => ['application/json'],
            'content-type' => ['application/json']
          ],
        'json' => [ 'username' => 'autreUsername']
      ]
    );
    $this->assertResponseStatusCodeSame(200);
  }

  public function testAnAdminUserShouldBeAbleToMakeWhateverHeLike()
  {
    $client = self::createClient();
    $user = $this->createUser($client,'testuser@mail.com','password');
    $admin = $this->createUser($client,'admin@mail.com','password',['ROLE_ADMIN']);
    $this->loginAsUser($client,$admin->getEmail(),'password');
    $client->request('PUT','/api/users/' . $user->getId(),
      [
        'headers' =>
          ['accept' => ['application/json'],
            'content-type' => ['application/json']
          ],
        'json' => [ 'username' => 'autreUsername']
      ]
    );
  }

  public function testTheCreationOfAnUserShouldBePossible()
  {
    $client = self::createClient();
    $client->request('POST','/api/users',
    [
      'headers' => $this->goodHeaders,
      'json' => [
        'email' => 'usermail@mail.com',
        'username' => 'username',
        'password' => 'password'
      ]
    ]);

    $this->assertResponseStatusCodeSame(201);

    $client->request('POST','/login',
    [
      'headers' => $this->goodHeaders,
      'json' => [
        'email' => 'usermail@mail.com',
        'password' => 'password'
      ]
    ]);

    $this->assertResponseStatusCodeSame(204);
  }
}
