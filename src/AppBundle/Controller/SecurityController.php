<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
* @Route("/{_locale}/login", requirements={"_locale" = "%app.locales%"})
*/
class SecurityController extends Controller
{
    /**
     * @Route("/", name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authUtils)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();
        $locale = $request->getLocale();
        if ($locale == 'es') {
            $pages = $repository->findBy(array(
                'navbar' => '1',
                'status' => 'publish'), array(
                'titleEs' => 'ASC'
            ));
        } else {
            $pages = $repository->findBy(array(
                'navbar' => '1',
                'status' => 'publish'), array(
                'titleEn' => 'ASC'
            ));
        }
        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'pages' => $pages
    ));
    }
}
