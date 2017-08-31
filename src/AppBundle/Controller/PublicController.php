<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/{_locale}", requirements={"_locale" = "%app.locales%"})
 */
class PublicController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $posts = $repository->findBy(array(), array('date' => 'DESC'));
        return $this->render('public/index.html.twig',
                array('posts' => $posts));
    }

    /**
     * @Route("/{slug}", name="post")
     */
    public function postAction(Post $post)
    {
        return $this->render('public/post.html.twig', array(
                'post' => $post));
    }
}
