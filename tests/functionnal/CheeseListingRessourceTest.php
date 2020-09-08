<?php


namespace App\Tests\functionnal;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class CheeseListingRessourceTest extends ApiTestCase
{
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
}
