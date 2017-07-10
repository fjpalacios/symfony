<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
        $locale = $request->getLocale();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Posts');
        $posts = $repository->findAll();
        return $this->render('homepage/index.html.twig', array('posts' => $posts));
    }
    /**
     * @Route("/about", name="about")
     */
    public function aboutAction(Request $request)
    {
        $locale = $request->getLocale();
        return $this->render('homepage/about.html.twig');
    }


    public function localeAction($route = 'homepage', $parameters = array())
    {
        $this->getRequest()->setLocale($this->getRequest()->getPreferredLanguage($this->container->getParameter('locale_supported')));
        return $this->redirect($this->generateUrl($route, $parameters));
    }
}
