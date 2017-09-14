<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
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
     * @Route("/", name="home", defaults={"_format"="html"})
     * @Route("/rss/", name="rss", defaults={"_format"="xml"})
     */
    public function indexAction(Request $request, $_format)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $posts = $repository->findBy(array(
                'status' => 'publish',
                'type' => 'post'), array(
                'date' => 'DESC'
        ));
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
        return $this->render('public/index.'.$_format.'.twig', array(
                'posts' => $posts,
                'pages' => $pages
        ));
    }

    /**
     * @Route("/{slug}", name="post")
     */
    public function postAction(Request $request, Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $postRepo = $this->getDoctrine()->getRepository('AppBundle:Post');
        $author = $userRepo->find($post->getAuthor());
        $locale = $request->getLocale();
        if ($locale == 'es') {
            $pages = $postRepo->findBy(array(
                    'navbar' => '1',
                    'status' => 'publish'), array(
                    'titleEs' => 'ASC'
            ));
        } else {
            $pages = $postRepo->findBy(array(
                    'navbar' => '1',
                    'status' => 'publish'), array(
                    'titleEn' => 'ASC'
            ));
        }
        $categoryRepo = $em->getRepository('AppBundle:Category');
        $categories = $categoryRepo->findAll();
        $slug = $request->attributes->get('slug');
        if ($slug == 'categorias') {
            return $this->render('public/categories.html.twig', array(
                'pages' => $pages,
                'categories' => $categories
            ));
        } elseif ($slug == 'login') {
            return $this->redirectToRoute('login');
        } else {
            return $this->render('public/post.html.twig', array(
                'post' => $post,
                'user' => $author,
                'pages' => $pages
            ));
        }
    }

    /**
     * @Route("/profile/{username}", name="profile")
     */
    public function profileAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $postRepo = $em->getRepository('AppBundle:Post');
        $posts = $postRepo->findBy(array(
            'author' => $user->getId(),
            'type' => 'post',
            'status' => 'publish'), array(
            'date' => 'DESC'
        ));
        $locale = $request->getLocale();
        if ($locale == 'es') {
            $pages = $postRepo->findBy(array(
                'navbar' => '1',
                'status' => 'publish'), array(
                'titleEs' => 'ASC'
            ));
        } else {
            $pages = $postRepo->findBy(array(
                'navbar' => '1',
                'status' => 'publish'), array(
                'titleEn' => 'ASC'
            ));
        }
        return $this->render('public/profile.html.twig', array(
            'posts' => $posts,
            'user' => $user,
            'pages' => $pages
        ));
    }

    /**
     * @Route("/category/{slug}", name="category")
     */
    public function categoryAction(Request $request, Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $postRepo = $em->getRepository('AppBundle:Post');
        $posts = $postRepo->findBy(array(
            'category' => $category->getId(),
            'type' => 'post',
            'status' => 'publish'), array(
            'date' => 'DESC'
        ));
        $locale = $request->getLocale();
        if ($locale == 'es') {
            $pages = $postRepo->findBy(array(
                'navbar' => '1',
                'status' => 'publish'), array(
                'titleEs' => 'ASC'
            ));
        } else {
            $pages = $postRepo->findBy(array(
                'navbar' => '1',
                'status' => 'publish'), array(
                'titleEn' => 'ASC'
            ));
        }
        return $this->render('public/category.html.twig', array(
            'posts' => $posts,
            'category' => $category,
            'pages' => $pages
        ));
    }
}
