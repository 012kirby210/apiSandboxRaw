<?php


namespace App\Tests\BaseClasses;

use \ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\User;

class UserFriendlyTestCase extends ApiTestCase
{

  public function createUser(?Client $client,?string $email,?string $password,
                             ?array $roles = []) :User
  {
    $user = new User();
    $userName = strstr($email,'@', true);
    $password = self::$container->get('security.password_encoder')->encodePassword($user,$password);
    $entityManager = self::$container->get('doctrine.orm.default_entity_manager');
    $user->setEmail($email);
    $user->setPassword($password);
    $user->setUsername($userName);
    !empty($roles) && array_push($roles,'ROLE_USER');
    !empty($roles) && $user->setRoles($roles);
    $entityManager->persist($user);
    $entityManager->flush();
    return $user;
  }

  public function loginAsUser(Client $client,?string $email,?string $password)
  {
    $client->request('POST','/login',
    [
      'headers' => [
        'accept' => ['application/json'],
        'Content-type' => ['application/json']
        ],
      'json' => [
        'email' => $email,
        'password' => $password
      ]
    ]);
    $this->assertResponseStatusCodeSame(204);
  }

}
