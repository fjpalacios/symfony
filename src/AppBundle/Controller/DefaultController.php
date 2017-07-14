<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
* @Route("/{_locale}", requirements={"_locale" = "%app.locales%"})
*/
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Posts');
        $posts = $repository->findAll();
        return $this->render('homepage/index.html.twig', array('posts' => $posts));
    }
    /**
     * @Route("/about", name="about")
     */
    public function aboutAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute('homepage');
        }

        return $this->render('homepage/about.html.twig',
            array("form" => $form->createView())
        );
    }


    public function localeAction($route = 'homepage', $parameters = array())
    {
        $this->getRequest()->setLocale($this->getRequest()->getPreferredLanguage($this->container->getParameter('locale_supported')));
        return $this->redirect($this->generateUrl($route, $parameters));
    }
}
