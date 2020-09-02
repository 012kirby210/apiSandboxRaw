<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Use to instanciate the vue js twig wrapper
 * Class FrontendController
 * @package App\Controller
 */
class FrontendController extends AbstractController
{
  public function homepage(SerializerInterface $serializer)
  {
    return $this->render('frontend/homepage.html.twig',
      [ 'user' => $serializer->serialize($this->getUser(),'jsonld')
      ]
    );
  }
}
