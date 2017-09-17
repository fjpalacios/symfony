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
     * @Route("/", name="home", defaults={"_format"="html", "page": "1"})
     * @Route("/page/{page}", name="paginated_home", defaults={"_format"="html"}, requirements={"page": "[1-9]\d*"})
     * @Route("/rss/", name="rss", defaults={"_format"="xml", "page": "1"})
     */
    public function indexAction(Request $request, $_format, $page)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $route = $request->attributes->get('_route');
        if ($route == 'rss') {
            $posts = $repository->getPaginatedIndex($page, Post::RSS_NUM_ITEMS);
            $totalItems = count($posts);
            $pagesCount = ceil($totalItems / Post::RSS_NUM_ITEMS);
        } else {
            $posts = $repository->getPaginatedIndex($page);
            $totalItems = count($posts);
            $pagesCount = ceil($totalItems / Post::NUM_ITEMS);
        }
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
                'pages' => $pages,
                'totalItems' => $totalItems,
                'pagesCount' => $pagesCount,
                'page' => $page
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
        $categories = $categoryRepo->findBy(array(), array('name' => 'ASC'));
        $slug = $request->attributes->get('slug');
        if (!$this->get('security.authorization_checker')
            ->isGranted('ROLE_ADMIN')) {
            $postView = $postRepo->find($post->getId());
            $postView->setViews($postView->getViews() + 1);
            $em->flush();
        }
        if ($slug == 'categorias') {
            return $this->render('public/categories.html.twig', array(
                'pages' => $pages,
                'categories' => $categories
            ));
        } elseif ($slug == 'rss') {
            return $this->redirectToRoute('rss');
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
     * @Route("/profile/{username}", name="profile", defaults={"page": "1"})
     * @Route("/profile/{username}/page/{page}", name="paginated_profile", requirements={"page": "[1-9]\d*"})
     */
    public function profileAction(Request $request, User $user, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $postRepo = $em->getRepository('AppBundle:Post');
        $posts = $postRepo->getPaginatedProfile($page, $user->getId());
        $totalItems = count($posts);
        $pagesCount = ceil($totalItems / Post::NUM_ITEMS);
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
            'pages' => $pages,
            'totalItems' => $totalItems,
            'pagesCount' => $pagesCount,
            'page' => $page,
            'username' => $user->getUsername()
        ));
    }

    /**
     * @Route("/category/{slug}", name="category", defaults={"page": "1"})
     * @Route("/category/{slug}/page/{page}", name="paginated_category", requirements={"page": "[1-9]\d*"})
     */
    public function categoryAction(Request $request, Category $category, $page)
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
