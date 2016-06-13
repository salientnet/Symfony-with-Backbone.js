<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\Type\UserType ;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /** @var  User */
    private $user;

    /**
     * @Route("/", name="homepage")
      * @Template()
      */
     public function indexAction()
     {
         return array();
     }

    /**
     * Handles user form ajax submission
     *
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @Route("/process-form", name="process.form", options={"expose"=true})
     */
    public function processFormAction()
    {
        $this->initUser();
        $handler = $this->get('frontend.form_handlers.user')->setModel($this->user);

        return $handler->handle();
    }

    /**
     * Inits current user
     */
    private function initUser()
    {
        $userRepo = $this->get('doctrine')->getRepository('AppBundle:User');
        $user = $userRepo->findOneBy(array('idToken' => '5dsf4dsf5sdf4'));
        if (!$user instanceof User) {
            throw $this->createNotFoundException('User not found, please load fixtures');
        }

        $this->user = $user;
    }


    /**
    *
    * @Route("/api/entries/{id}")
    */
    public function apiEntryUpdateAction()
    {
      $request = $this->get('request');

      $params = array();
      $content = $request->getContent();
      if (!empty($content)) {
          $params = json_decode($content, true); // 2nd param to get as array
      }

      if ($request->getMethod() == 'PUT') {
          if (empty($params['id'])) {
               $errors = array ('errors' => array('id' => array('id can\'t be blank')));
               $response = new Response(json_encode($errors));
               $response->setStatusCode(422);

               return $response;
          }

          $em = $this->getDoctrine()->getManager();
          $entity = $em->getRepository('AppBundle:User')->find($params['id']);
          $form = $this->createForm(new UserType(), $entity);
          $form->handleRequest($request);
          $entity->setUserName($params['user_name']);
          $entity->setUserEmail($params['user_email']);
          $entity->setUserBirthday(new \DateTime($params['user_birthday']));
          $entity->setUserAbout($params['user_about']);
          $entity->setUserGender($params['user_gender']);
          $entity->setUserPhone($params['user_phone']);
          $entity->setUserSkill($params['user_skill']);
          $entity->setSiteUrl($params['site_url']);
          $entity->setPassword($params['password']);
          $em->persist($entity);

          $em->flush();
          $response = new Response();
          $response->headers->set('Content-Type', 'application/json');
          $serializer = $this->container->get('serializer');
          $entries = $serializer->serialize($entity, 'json');
          $output = array('success' => true, 'data' => $entries);
          $response->setContent(json_encode($entries));

          return $response;
      }
    }

    /**
    *
    * @Route("/api/entries")
    */
    public function apiEntriesAction()
    {
      $userRepo = $this->get('doctrine')->getRepository('AppBundle:User');
      $user = $userRepo->findOneBy(array('idToken' => '5dsf4dsf5sdf4'));
      $serializer = $this->container->get('serializer');
      $entries = $serializer->serialize($user, 'json');
      $request = $this->get('request');

      $params = array();
      $content = $request->getContent();
      if (!empty($content)) {
          $params = json_decode($content, true); // 2nd param to get as array
      }

      if ($request->getMethod() == 'GET') {
          return new Response($entries);
      } else if ($request->getMethod() == 'POST') {
          if (empty($params['userName'])) {
               $errors = array ('errors' => array('userName' => array('name can\'t be blank')));
               $response = new Response(json_encode($errors));
               $response->setStatusCode(422);

               return $response;
          }

          array_push($entries, array('userName' =>$params['name']));
          return new Response(json_encode($entries));
     }
  }
}
