<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Comment;
use AppBundle\Entity\Course;
use AppBundle\Entity\Post;
use AppBundle\Form\CommentType;
use AppBundle\Form\ContactType;
use AppBundle\Form\SearchFormType;
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
        return $this->render('public/index.' . $_format . '.twig', array(
            'posts' => $posts,
            'totalItems' => $totalItems,
            'pagesCount' => $pagesCount,
            'page' => $page
        ));
    }

    /**
     * @Route("/article/{slug}/", name="post")
     */
    public function postAction(Request $request, Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $postRepo = $this->getDoctrine()->getRepository('AppBundle:Post');
        $author = $userRepo->find($post->getAuthor());
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
            $env = $this->container->get('kernel')->getEnvironment();
            $commentAuthor = $form->get('author')->getData();
            $commentEmail = $form->get('email')->getData();
            $commentComment = $form->get('comment')->getData();
            if ($commentAuthor) {
                if ($commentEmail) {
                    if ($commentComment) {
                        $comment->setDate(new \DateTime('now'));
                        if ($env != 'test') {
                            $wpApi = $this->container
                                ->getParameter('wordpress_api_key');
                            $wpUrl = $this->container
                                ->getParameter('wordpress_blog_url');
                            $akismet = new Akismet($wpUrl, $wpApi);
                            $akismet->setCommentAuthorEmail($comment->getEmail());
                            $akismet->setCommentContent($comment->getComment());
                            if ($akismet->isCommentSpam()) {
                                $comment->setStatus('pending');
                            } else {
                                $comment->setStatus('approved');
                            }
                        } else {
                            $comment->setStatus('approved');
                        }
                        $comment->setPostId($post->getId());
                        $comment->setIp($request->getClientIp());
                        $em->persist($comment);
                        $flush = $em->flush();
                        $id = $comment->getId();
                        if (!$flush) {
                            $securityContext = $this->container->get('security.authorization_checker');
                            $currentUserEmail = '';
                            if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                                $currentUserId = $this->get('security.token_storage')->getToken()->getUser();
                                $currentUserEmail = $userRepo->find($currentUserId)->getEmail();
                            }
                            if ($env != 'test' && $akismet->isCommentSpam()) {
                                if ($comment->getEmail() != $currentUserEmail) {
                                    $this->container->get('system_mailer')
                                        ->send('App:new-comment-spam', array(
                                            'comment' => $comment,
                                            'post' => $post
                                        ), 'es');
                                }
                                $status = 'COMMENT_SPAM_DETECTED';
                            } else {
                                if ($env != 'test' && $comment->getEmail() != $currentUserEmail) {
                                    $this->container->get('system_mailer')
                                        ->send('App:new-comment', array(
                                            'comment' => $comment,
                                            'post' => $post
                                        ), 'es');
                                }
                                $status = 'COMMENT_ADDED_PROPERLY';
                                $commentCount = $postRepo->find($post->getId());
                                $commentCount->setCommentCount($commentCount->getCommentCount() + 1);
                                $em->flush();
                                $this->session->getFlashBag()->add('status', $status);
                                return $this->redirectToRoute('post', array(
                                    'slug' => $slug,
                                    '_fragment' => 'comment-' . $id
                                ));
                            }
                        } else {
                            $status = 'COMMENT_ADDED_ERROR';
                        }
                    } else {
                        $status = 'COMMENT_ADDED_ERROR_COMMENT';
                    }
                } else {
                    $status = 'COMMENT_ADDED_ERROR_EMAIL';
                }
            } else {
                $status = 'COMMENT_ADDED_ERROR_AUTHOR';
            }
            $this->session->getFlashBag()->add('status', $status);
            return $this->render('public/post.html.twig', array(
                'post' => $post,
                'user' => $author,
                'comments' => $comments,
                'form' => $form->createView()
            ));
        }
        return $this->render('public/post.html.twig', array(
            'post' => $post,
            'user' => $author,
            'comments' => $comments,
            'form' => $form->createView()
        ));
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
        return $this->render('public/profile.html.twig', array(
            'posts' => $posts,
            'user' => $user,
            'totalItems' => $totalItems,
            'pagesCount' => $pagesCount,
            'page' => $page,
            'username' => $user->getUsername()
        ));
    }

    /**
     * @Route("/categories/", name="categories")
     */
    public function categoriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categoryRepo = $em->getRepository('AppBundle:Category');
        $locale = $request->getLocale();
        if ($locale == 'es') {
            $categories = $categoryRepo->findBy(array(), array('nameEs' => 'ASC'));
        } else {
            $categories = $categoryRepo->findBy(array(), array('nameEn' => 'ASC'));
        }
        return $this->render('public/categories.html.twig', array(
            'categories' => $categories
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
        $posts = $postRepo->getPaginatedCategory($page, $category->getId());
        $totalItems = count($posts);
        $pagesCount = ceil($totalItems / Post::NUM_ITEMS);
        return $this->render('public/category.html.twig', array(
            'posts' => $posts,
            'category' => $category,
            'totalItems' => $totalItems,
            'pagesCount' => $pagesCount,
            'page' => $page,
            'slug' => $category->getSlug()
        ));
    }

    /**
     * @Route("/courses/", name="courses")
     */
    public function coursesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $courseRepo = $em->getRepository('AppBundle:Course');
        $locale = $request->getLocale();
        if ($locale == 'es') {
            $courses = $courseRepo->findBy(array(), array('nameEs' => 'ASC'));
        } else {
            $courses = $courseRepo->findBy(array(), array('nameEn' => 'ASC'));
        }
        return $this->render('public/courses.html.twig', array(
            'courses' => $courses
        ));
    }

    /**
     * @Route("/course/{slug}", name="course", defaults={"page": "1"})
     * @Route("/course/{slug}/page/{page}", name="paginated_course", requirements={"page": "[1-9]\d*"})
     */
    public function courseAction(Request $request, Course $course, $page)
    {
        $em = $this->getDoctrine()->getManager();
        $postRepo = $em->getRepository('AppBundle:Post');
        $posts = $postRepo->getPaginatedCourse($page, $course->getId());
        $totalItems = count($posts);
        $pagesCount = ceil($totalItems / Post::NUM_ITEMS);
        return $this->render('public/course.html.twig', array(
            'posts' => $posts,
            'course' => $course,
            'totalItems' => $totalItems,
            'pagesCount' => $pagesCount,
            'page' => $page,
            'slug' => $course->getSlug()
        ));
    }

    /**
     * @Route("/contact/", name="contact")
     */
    public function contactAction(Request $request)
    {
        $form = $this->createForm(ContactType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->get('name')->getData();
            $email = $form->get('email')->getData();
            $ip = $request->getClientIp();
            $subject = $form->get('subject')->getData();
            $message = $form->get('message')->getData();
            $this->container->get('system_mailer')
                ->send('App:contact', array(
                    'name' => $name,
                    'email' => $email,
                    'ip' => $ip,
                    'subject' => $subject,
                    'message' => $message
                ), 'es');
            $status = 'MESSAGE_SENDED_PROPERLY';
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('contact');
        }
        return $this->render('public/contact.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/about-us/", name="about_us")
     */
    public function aboutUsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $postRepo = $em->getRepository('AppBundle:Post');
        $post = $postRepo->findOneBy(array(
            'slug' => 'about-us'
        ));
        if (!$this->get('security.authorization_checker')
            ->isGranted('ROLE_ADMIN')) {
            $post->setViews($post->getViews() + 1);
            $em->flush();
        }
        return $this->render('public/post.html.twig', array(
            'post' => $post
        ));
    }

    /**
     * @Route("/search/{search}/", name="search", defaults={"page": "1"})
     * @Route("/search/", name="search_form", defaults={"page": "1"})
     */
    public function searchAction(Request $request, $page)
    {
        $search = $request->attributes->get('search');
        $form = $this->createForm(SearchFormType::class, null);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $searchForm = $form->get('search')->getData();
            return $this->redirectToRoute('search', array('search' => $searchForm));
        }
        if ($search) {
            $locale = $request->getLocale();
            $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
            $posts = $repository->getPaginatedSearch($locale, $search, $page);
            $totalItems = count($posts);
            $pagesCount = ceil($totalItems / Post::NUM_ITEMS);
            return $this->render('public/search.html.twig', array(
                'posts' => $posts,
                'totalItems' => $totalItems,
                'pagesCount' => $pagesCount,
                'page' => $page,
                'search' => $search
            ));
        }
        return $this->render('public/search.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function getCommentChildAction($id)
    {
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
