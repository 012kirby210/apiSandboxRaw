<?php

namespace App\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{

  /**
   * @Route("/login", name="app_login", methods={ "POST" })
   */
  public function login(IriConverterInterface $iriConverter){
    if (!$this->isGranted('IS_AUTHENTICATED_FULLY')){
      return $this->json([
        'error' => 'Invalid login request : check that the content-type header is "application/json".'
      ],400);
    }
    return new Response(null,204, [ 'Location' => $iriConverter->getIriFromItem($this->getUser())]);
  }

  /**
   * @Route("/logout",name="app_logout")
   * @throws \Exception assure the code that on reach an exception will be thrown
   */
  public function logout()
  {
    // All the requests to the logout controller should be intercepted
    // by the security configuration
    throw new \Exception('should never be reached');
  }
}
