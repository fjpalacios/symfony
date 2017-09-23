<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Post;
use AppBundle\Form\CommentType;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use AppBundle\Utils\Akismet;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Route("/{_locale}", requirements={"_locale" = "%app.locales%"})
 */
class PublicController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

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
        $commentRepo = $em->getRepository('AppBundle:Comment');
        $comments = $commentRepo->findBy(array(
            'postId' => $post->getId(),
            'status' => 'approved',
            'parentId' => null), array(
                'date' => 'ASC'
            )
        );
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setDate(new \DateTime('now'));
            $wpApi = $this->container
                ->getParameter('wordpress_api_key');
            $wpUrl = $this->container
                ->getParameter('wordpress_blog_url');
            $akismet = new Akismet($wpUrl ,$wpApi);
            $akismet->setCommentAuthorEmail($comment->getEmail());
            $akismet->setCommentContent($comment->getComment());
            if($akismet->isCommentSpam()) {
                $comment->setStatus('pending');
            } else {
                $comment->setStatus('approved');
            }
            $comment->setPostId($post->getId());
            $comment->setIp($request->getClientIp());
            $em->persist($comment);
            $flush = $em->flush();
            $id = $comment->getId();
            if (!$flush) {
                if($akismet->isCommentSpam()) {
                    $status = 'COMMENT_SPAM_DETECTED';
                } else {
                 $status = 'COMMENT_ADDED_PROPERLY';
                }
            } else {
                $status = 'COMMENT_ADDED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
            if($akismet->isCommentSpam()) {
                return $this->redirectToRoute('post', array(
                    'slug' => $slug
                ));
            } else {
                return $this->redirectToRoute('post', array(
                    'slug' => $slug,
                    '_fragment' => 'comment-' . $id
                ));
            }
        }
        if ($slug == 'categorias') {
            return $this->render('public/categories.html.twig', array(
                'pages' => $pages,
                'categories' => $categories
            ));
        } else {
            return $this->render('public/post.html.twig', array(
                'post' => $post,
                'user' => $author,
                'pages' => $pages,
                'comments' => $comments,
                'form' => $form->createView()
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

    public function getCommentChildAction($id) {
        $em = $this->getDoctrine()->getManager();
        $commentRepo = $em->getRepository('AppBundle:Comment');
        $comments = $commentRepo->findBy(array(
            'parentId' => $id,
            'status' => 'approved'), array(
                'date' => 'ASC'
            )
        );
        return $this->render('public/_comments.html.twig', array(
            'comments' => $comments
        ));
    }
}
