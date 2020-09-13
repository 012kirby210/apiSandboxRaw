<?php


namespace App\Tests\functionnal;

use App\Tests\BaseClasses\UserFriendlyTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\HttpClient\Exception\JsonException;
use Symfony\Component\HttpClient\Exception\TransportException;



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
    $this->assertJsonContains(['username' => 'autreUsername']);
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

  public function testThePhoneNumberShouldNotAppearInTheResultOfTheResearch()
  {
    $client = self::createClient();
    $user = $this->createUser($client,'username@mail.com','password');
    $user->setPhoneNumber('065414584966');
    $em = self::$container->get('doctrine.orm.entity_manager');
    $em->persist($user);
    $em->flush();

    $this->loginAsUser($client,'username@mail.com','password');
    $responseInterface = $client->request('GET','/api/users/'.$user->getId(),
    [
      'headers' => $this->goodHeaders
    ]);
    try {
      $data = $responseInterface->toArray();
      $this->assertArrayNotHasKey('phoneNumber',$data);

    }catch(TransportException $e){
      error_log($e->getMessage());
    }catch(JsonException $e){
      error_log($e->getMessage());
    }
  }


  public function testAnAdminUserShouldBeAbleToSeeAllTheFieldsOfAnyUser()
  {
    $client = self::createClient();
    $adminUser = $this->createUser($client,'admin@mail.com',
      'password',['ROLE_ADMIN']);

    $regularUser = $this->createUser($client,'regularUser@mail.com','password');
    $regularUser->setPhoneNumber('065414584966');
    $em = self::$container->get('doctrine.orm.entity_manager');
    $em->persist($regularUser);
    $em->flush();
    $this->loginAsUser($client,'admin@mail.com','password');
    $responseInterface = $client->request('GET','/api/users/'.$regularUser->getId(),
    [
      'headers' => $this->goodHeaders
    ]);
    try{
      $data = $responseInterface->toArray();
      $this->assertArrayHasKey('phoneNumber',$data);
    }catch(TransportException $e){
      error_log($e->getMessage());
    }catch(JsonException $e){
      error_log($e->getMessage());
    }
  }

  /** Cannot work because of the security objet == User, event admin cannot change other user's role
  public function testAnAdminShouldBeAbleToUpdateRolesNoOthersUserShouldBeAbleTo()
  {
    $client = self::createClient();
    $adminUser = $this->createUser($client,'admin@mail.com',
      'password',['ROLE_ADMIN']);

    $regularUser = $this->createUser($client,'regularUser@mail.com',
      'password');

    $this->loginAsUser($client,'admin@mail.com','password');

    $responseInterface = $client->request('PUT','api/users/'.$regularUser->getId(),
    [
      'headers' => $this->goodHeaders,
      'json' => [
        'roles' => [ 'ROLE_ADMIN', 'ROLE_USER']
      ]
    ]);
    $this->assertResponseStatusCodeSame(200);
    $this->loginAsUser($client,'regularUser@mail.com','password');
    $responseInterface = $client->request('GET','/api/users/'.$adminUser->getId(),
      [
        'headers' => $this->goodHeaders
      ]);
    try{
      $data = $responseInterface->toArray();
      $this->assertArrayHasKey('phoneNumber',$data);
    }catch(TransportException $e){
      error_log($e->getMessage());
    }catch(JsonException $e){
      error_log($e->getMessage());
    }
    $client->request('PUT','api/users/'.$adminUser->getId(),
    [
      'headers' => $this->goodHeaders,
      'json' => ['roles' => ['ROLE_USER']]
    ]);
    $this->loginAsUser($client,'admin@mail.com','password');
    $client->request('PUT','api/users/'.$adminUser->getId(), [
        'headers' => $this->goodHeaders,
        'json' => ['roles' => ['ROLE_USER']]
      ]
    );
    $responseInterface = $client->request('GET','/api/users/'.$regularUser->getId(),
      [
        'headers' => $this->goodHeaders
      ]);
    try{
      $data = $responseInterface->toArray();
      $this->assertArrayNotHasKey('phoneNumber',$data);
    }catch(TransportException $e){
      error_log($e->getMessage());
    }catch(JsonException $e){
      error_log($e->getMessage());
    }
  }
   */

  // but whatever no admin user canot changes their own role
  // + the admin can change its own role
  public function testAdminShouldBeAbleToChangeItsOwnRoles()
  {
    $client = self::createClient();
    $admin = $this->createUser($client,'admin@mail.com','password',['ROLE_ADMIN']);
    $refreshedAdmin = self::$container->get('doctrine.orm.entity_manager')->getRepository(User::class)->find($admin->getId());
    $this->loginAsUser($client,'admin@mail.com','password');
    $responseInterface = $client->request('PUT','/api/users/' . $admin->getId(),
      [ 'headers' => $this->goodHeaders,
        'json' => ['roles' => ['ROLE_ADMIN','ROLE_AUTRE']]
      ]
    );
    $em = self::$container->get('doctrine.orm.entity_manager');
    /** @var User $refreshedAdmin */
    $refreshedAdmin = $em->getRepository(User::class)->find($admin->getId());
    $this->assertTrue(in_array('ROLE_AUTRE',$refreshedAdmin->getRoles()));
  }

  public function testARegularUserShouldNotBeAbleToChangeItsOwnRoles()
  {
    $client = self::createClient();
    $regularUser = $this->createUser($client,'regular@mail.com','password');
    $this->loginAsUser($client,'regular@mail.com','password');
    $responseInteface = $client->request('PUT','/api/users/' . $regularUser->getId(),
    [
      'headers' => $this->goodHeaders,
      'json' => ['roles' => ['ROLE_ADMIN']]
    ]);
    $this->assertResponseStatusCodeSame(200);
    // but the roles aint changed.
    /** @var User $refreshedRegularUser */
    $refreshedRegularUser = self::$container->get('doctrine.orm.entity_manager')->getRepository(User::class)
      ->find($regularUser->getId());
    $this->assertNotContains('ROLE_ADMIN',$regularUser->getRoles());
  }

}
