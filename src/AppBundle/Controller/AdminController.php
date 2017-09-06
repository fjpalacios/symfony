<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Cocur\Slugify\Slugify;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Route("/{_locale}/admin", requirements={"_locale" = "%app.locales%"})
 */
class AdminController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @Route("/", name="admin")
     */
    public function adminAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userRepo = $em->getRepository('AppBundle:User');
            $userEmail = $userRepo->findOneBy(array(
                            'email' => $form->get('email')->getData()
                    )
            );
            $userUsername = $userRepo->findOneBy(array(
                            'username' => $form->get('username')->getData()
                    )
            );
            if (!$userEmail) {
                if (!$userUsername) {
                    $password = $passwordEncoder->encodePassword($user,
                            $user->getPlainPassword());
                    $user->setPassword($password);
                    $em->persist($user);
                    $flush = $em->flush();
                    if (!$flush) {
                        $status = 'USER_ADDED_PROPERLY';
                    } else {
                        $status = 'USER_ADDED_ERROR';
                    }
                } else {
                    $status = 'USER_ADDED_USERNAME_EXIST';
                }
            } else {
                $status = 'USER_ADDED_EMAIL_EXIST';
            }
            $this->session->getFlashBag()->add('status', $status);
        }
        return $this->render('admin/admin.html.twig',
                array("form" => $form->createView())
        );
    }

    /**
     * @Route("/posts", name="admin_posts")
     */
    public function postsAction()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $posts = $repository->findBy(array('type' => 'post'), array('date' => 'DESC'));
        return $this->render('admin/posts.html.twig', array(
                'posts' => $posts
        ));
    }

    /**
     * @Route("/posts/add", name="admin_posts_add")
     */
    public function postsAddAction(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $slug = new Slugify();
            $userRepo = $em->getRepository('AppBundle:User');
            $userId = $userRepo->find($this->getUser()->getId());
            $post->setAuthor($userId);
            $post->setDate(new \DateTime('now'));
            $post->setModDate(new \DateTime('now'));
            $post->setSlug($slug->slugify($post->getTitleEs()));
            $post->setType('post');
            $post->setNavbar(0);
            $post->setCommentCount(0);
            $post->setViews(0);
            $em->persist($post);
            $flush = $em->flush();
            $id = $post->getId();
            if (!$flush) {
                $status = 'POST_ADDED_PROPERLY';
            } else {
                $status = 'POST_ADDED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('admin_posts_edit', array(
                    'id' => $id
            ));
        }
        return $this->render('admin/posts-add.html.twig', array(
                'form' => $form->createView()));
    }

    /**
     * @Route("/posts/del/{id}", name="admin_posts_del")
     */
    public function postsRemoveAction(Post $post)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'ONLY_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $flush = $em->flush();
        if (!$flush) {
            $status = 'POST_REMOVED_PROPERLY';
        } else {
            $status = 'POST_REMOVED_ERROR';
        }
        $this->session->getFlashBag()->add('status', $status);
        return $this->redirectToRoute('admin_posts');
    }

    /**
     * @Route("/posts/edit/{id}", name="admin_posts_edit")
     */
    public function postsEditAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $postRepo = $em->getRepository('AppBundle:Post');
        $post = $postRepo->find($id);
        $author = $post->getAuthor();
        $status = $post->getStatus();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setModDate(new \DateTime('now'));
            if ($status == 'draft' && $form->get('status')->getData() != "draft") {
                $post->setDate(new \DateTime('now'));
            }
            $slug = new Slugify();
            $post->setSlug($slug->slugify($form->get('slug')->getData()));
            $post->setNavbar(0);
            $em->persist($post);
            $flush = $em->flush();
            if (!$flush) {
                $status = 'POST_EDITED_PROPERLY';
            } else {
                $status = 'POST_EDITED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('admin_posts_edit', array(
                    'id' => $id
            ));
        }
        return $this->render('admin/posts-edit.html.twig', array(
                'form' => $form->createView(),
                'author' => $author,
                'id' => $id
        ));
    }

    /**
     * @Route("/posts/view/{id}", name="admin_posts_view")
     */
    public function postsViewAction(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $author = $userRepo->find($post->getAuthor());
        return $this->render('admin/posts-view.html.twig', array(
                'post' => $post,
                'author' => $author
        ));
    }

    /**
     * @Route("/pages", name="admin_pages")
     */
    public function pagesAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $locale = $request->getLocale();
        if ($locale == 'es') {
            $pages = $repository->findBy(array('type' => 'page'), array('titleEs' => 'ASC'));
        } else {
            $pages = $repository->findBy(array('type' => 'page'), array('titleEn' => 'ASC'));
        }
        return $this->render('admin/pages.html.twig', array(
                'pages' => $pages
        ));
    }

    /**
     * @Route("/pages/add", name="admin_pages_add")
     */
    public function pagesAddAction(Request $request)
    {
        $page = new Post();
        $form = $this->createForm(PostType::class, $page);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $slug = new Slugify();
            $userRepo = $em->getRepository('AppBundle:User');
            $userId = $userRepo->find($this->getUser()->getId());
            $page->setAuthor($userId);
            $page->setDate(new \DateTime('now'));
            $page->setModDate(new \DateTime('now'));
            $page->setSlug($slug->slugify($page->getTitleEs()));
            $page->setType('page');
            $page->setCommentCount(0);
            $page->setViews(0);
            $em->persist($page);
            $flush = $em->flush();
            $id = $page->getId();
            if (!$flush) {
                $status = 'PAGE_ADDED_PROPERLY';
            } else {
                $status = 'PAGE_ADDED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('admin_posts_edit', array(
                    'id' => $id
            ));
        }
        return $this->render('admin/pages-add.html.twig', array(
                'form' => $form->createView()));
    }

    /**
     * @Route("/pages/del/{id}", name="admin_pages_del")
     */
    public function pagesRemoveAction(Post $post)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'ONLY_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $flush = $em->flush();
        if (!$flush) {
            $status = 'PAGE_REMOVED_PROPERLY';
        } else {
            $status = 'PAGE_REMOVED_ERROR';
        }
        $this->session->getFlashBag()->add('status', $status);
        return $this->redirectToRoute('admin_pages');
    }

    /**
     * @Route("/pages/edit/{id}", name="admin_pages_edit")
     */
    public function pagesEditAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $postRepo = $em->getRepository('AppBundle:Post');
        $page = $postRepo->find($id);
        $author = $page->getAuthor();
        $status = $page->getStatus();
        $form = $this->createForm(PostType::class, $page);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page->setModDate(new \DateTime('now'));
            if ($status == 'draft' && $form->get('status')->getData() != "draft") {
                $page->setDate(new \DateTime('now'));
            }
            $slug = new Slugify();
            $page->setSlug($slug->slugify($form->get('slug')->getData()));
            $em->persist($page);
            $flush = $em->flush();
            if (!$flush) {
                $status = 'PAGE_EDITED_PROPERLY';
            } else {
                $status = 'PAGE_EDITED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('admin_pages_edit', array(
                    'id' => $id
            ));
        }
        return $this->render('admin/pages-edit.html.twig', array(
                'form' => $form->createView(),
                'author' => $author,
                'id' => $id
        ));
    }

    /**
     * @Route("/pages/view/{id}", name="admin_pages_view")
     */
    public function pagesViewAction(Post $page)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $author = $userRepo->find($page->getAuthor());
        return $this->render('admin/pages-view.html.twig', array(
                'page' => $page,
                'author' => $author
        ));
    }

    /**
     * @Route("/users", name="admin_users")
     */
    public function usersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $users = $userRepo->findBy(array(), array('id' => 'ASC'));
        return $this->render('admin/users.html.twig', array(
            'users' => $users
        ));
    }

}
