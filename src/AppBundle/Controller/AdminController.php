<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Comment;
use AppBundle\Form\CategoryType;
use AppBundle\Entity\Post;
use AppBundle\Form\CommentType;
use AppBundle\Form\PostType;
use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Cocur\Slugify\Slugify;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
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
     * @Route("/posts/", name="admin_posts")
     */
    public function postsAction()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $posts = $repository->findBy(array('type' => 'post'), array('date' => 'DESC'));
        return $this->render('admin/posts/posts.html.twig', array(
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
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move("uploads", $fileName);
                $post->setImage($fileName);
            }
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
        return $this->render('admin/posts/posts-add.html.twig', array(
                'form' => $form->createView()));
    }

    /**
     * @Route("/posts/del/{id}", name="admin_posts_del")
     */
    public function postsRemoveAction(Post $post)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'ONLY_ADMIN');
        $image = $post->getImage();
        if ($image) {
            $fs = new Filesystem();
            $fs->remove($this->get('kernel')->getRootDir() .
                '/../web/uploads/' . $image);
        }
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
        $image = $post->getImage();
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
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move("uploads", $fileName);
                $post->setImage($fileName);
                if ($image) {
                    $fs = new Filesystem();
                    $fs->remove($this->get('kernel')->getRootDir() .
                        '/../web/uploads/' . $image);
                }
            } else {
                $post->setImage($image);
            }
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
        return $this->render('admin/posts/posts-edit.html.twig', array(
                'form' => $form->createView(),
                'author' => $author,
                'id' => $id,
                'post' => $post
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
        return $this->render('admin/posts/posts-view.html.twig', array(
                'post' => $post,
                'user' => $author
        ));
    }

    /**
     * @Route("/pages/", name="admin_pages")
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
        return $this->render('admin/pages/pages.html.twig', array(
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
        return $this->render('admin/pages/pages-add.html.twig', array(
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
        return $this->render('admin/pages/pages-edit.html.twig', array(
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
        return $this->render('admin/pages/pages-view.html.twig', array(
                'page' => $page
        ));
    }

    /**
     * @Route("/users/", name="admin_users")
     */
    public function usersAction()
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $users = $userRepo->findBy(array(), array('id' => 'ASC'));
        return $this->render('admin/users/users.html.twig', array(
            'users' => $users
        ));
    }

    /**
     * @Route("/users/add", name="admin_users_add")
     */
    public function usersAddAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
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
            $userPassword = $form->get('plainPassword')->getData();
            if ($userPassword) {
                if (!$userEmail) {
                    if (!$userUsername) {
                        $password = $passwordEncoder->encodePassword($user,
                            $user->getPlainPassword());
                        $user->setPassword($password);
                        $em->persist($user);
                        $flush = $em->flush();
                        $id = $user->getId();
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
            } else {
                $status = 'USER_ADDED_ERROR_PASSWORD';
            }
            $this->session->getFlashBag()->add('status', $status);
            if (isset($id)) {
                return $this->redirectToRoute('admin_users_edit', array(
                    'id' => $id
                ));
            }
        }
        return $this->render('admin/users/users-add.html.twig',
            array("form" => $form->createView())
        );
    }

    /**
     * @Route("/users/del/{id}", name="admin_users_del")
     */
    public function usersRemoveAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'ONLY_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $activeUserId = $userRepo->find($this->getUser()->getId());
        $userId = $userRepo->find($id);
        if ($userId != $activeUserId) {
            $em->remove($userId);
            $flush = $em->flush();
            if (!$flush) {
                $status = 'USER_REMOVED_PROPERLY';
            } else {
                $status = 'USER_REMOVED_ERROR';
            }
        } else {
            $status = 'DONT_REMOVE_YOURSELF';
        }
        $this->session->getFlashBag()->add('status', $status);
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route("/users/edit/{id}", name="admin_users_edit")
     */
    public function usersEditAction(Request $request, $id,
                                    UserPasswordEncoderInterface $passwordEncoder)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');
        $user = $userRepo->find($id);
        $roles = $user->getRoles();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('plainPassword')->getData()) {
                $password = $passwordEncoder->encodePassword($user,
                    $user->getPlainPassword());
                $user->setPassword($password);
            }
            if ($form->get('roles')->getData()) {
                $user->setRoles($form->get('roles')->getData());
            } else {
                $user->setRoles($roles);
            }
            $em->persist($user);
            $flush = $em->flush();
            if (!$flush) {
                $status = 'USER_EDITED_PROPERLY';
            } else {
                $status = 'USER_EDITED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('admin_users_edit', array(
                'id' => $id
            ));
        }
        return $this->render('admin/users/users-edit.html.twig', array(
            'form' => $form->createView(),
            'id' => $id
        ));
    }

    /**
     * @Route("/categories/", name="admin_categories")
     */
    public function categoriesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categoryRepo = $em->getRepository('AppBundle:Category');
        $categories = $categoryRepo->findBy(array(), array('name' => 'ASC'));
        return $this->render('admin/categories/categories.html.twig', array(
            'categories' => $categories
        ));
    }

    /**
     * @Route("/categories/add", name="admin_categories_add")
     */
    public function categoriesAddAction(Request $request)
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $slug = new Slugify();
            $category->setSlug($slug->slugify($category->getName()));
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move("uploads", $fileName);
                $category->setImage($fileName);
            }
            $em->persist($category);
            $flush = $em->flush();
            $id = $category->getId();
            if (!$flush) {
                $status = 'CATEGORY_ADDED_PROPERLY';
            } else {
                $status = 'CATEGORY_ADDED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('admin_categories_edit', array(
                'id' => $id
            ));
        }
        return $this->render('admin/categories/categories-add.html.twig', array(
            'form' => $form->createView()));
    }

    /**
     * @Route("/categories/del/{id}", name="admin_categories_del")
     */
    public function categoriesRemoveAction(Category $category)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'ONLY_ADMIN');
        $image = $category->getImage();
        if ($image) {
            $fs = new Filesystem();
            $fs->remove($this->get('kernel')->getRootDir() .
                '/../web/uploads/' . $image);
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $flush = $em->flush();
        if (!$flush) {
            $status = 'CATEGORY_REMOVED_PROPERLY';
        } else {
            $status = 'CATEGORY_REMOVED_ERROR';
        }
        $this->session->getFlashBag()->add('status', $status);
        return $this->redirectToRoute('admin_categories');
    }

    /**
     * @Route("/categories/edit/{id}", name="admin_categories_edit")
     */
    public function categoriesEditAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $categoryRepo = $em->getRepository('AppBundle:Category');
        $category = $categoryRepo->find($id);
        $image = $category->getImage();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = new Slugify();
            $category->setSlug($slug->slugify($form->get('slug')->getData()));
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = md5(uniqid()) . '.' . $file->guessExtension();
                $file->move("uploads", $fileName);
                $category->setImage($fileName);
                if ($image) {
                    $fs = new Filesystem();
                    $fs->remove($this->get('kernel')->getRootDir() .
                        '/../web/uploads/' . $image);
                }
            } else {
                $category->setImage($image);
            }
            $em->persist($category);
            $flush = $em->flush();
            if (!$flush) {
                $status = 'CATEGORY_EDITED_PROPERLY';
            } else {
                $status = 'CATEGORY_EDITED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('admin_categories_edit', array(
                'id' => $id
            ));
        }
        return $this->render('admin/categories/categories-edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/comments/", name="admin_comments")
     */
    public function commentsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $commentRepo = $em->getRepository('AppBundle:Comment');
        $comments = $commentRepo->getCommentsWithRelatedPost();
        return $this->render('admin/comments/comments.html.twig', array(
            'comments' => $comments
        ));
    }

    /**
     * @Route("/comments/pending/", name="admin_comments_pending")
     */
    public function commentsPendingAction()
    {
        $em = $this->getDoctrine()->getManager();
        $commentRepo = $em->getRepository('AppBundle:Comment');
        $comments = $commentRepo->getCommentsPendingWithRelatedPost();
        return $this->render('admin/comments/comments-pending.html.twig', array(
            'comments' => $comments
        ));
    }

    /**
     * @Route("/comments/approve/{id}", name="admin_comments_approve")
     */
    public function commentsApproveAction(Request $request, Comment $comment)
    {
        $em = $this->getDoctrine()->getManager();
        $comment->setStatus('approved');
        $flush = $em->flush();
        if (!$flush) {
            $status = 'COMMENT_APPROVED_PROPERLY';
        } else {
            $status = 'COMMENT_APPROVED_ERROR';
        }
        $this->session->getFlashBag()->add('status', $status);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/comments/del/{id}", name="admin_comments_del")
     */
    public function commentsRemoveAction(Request $request, Comment $comment)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'ONLY_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $flush = $em->flush();
        if (!$flush) {
            $status = 'COMMENT_REMOVED_PROPERLY';
        } else {
            $status = 'COMMENT_REMOVED_ERROR';
        }
        $this->session->getFlashBag()->add('status', $status);
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/comments/edit/{id}", name="admin_comments_edit")
     */
    public function commentsEditAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $commentRepo = $em->getRepository('AppBundle:Comment');
        $comment = $commentRepo->find($id);
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $flush = $em->flush();
            if (!$flush) {
                $status = 'COMMENT_EDITED_PROPERLY';
            } else {
                $status = 'COMMENT_EDITED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
            return $this->redirectToRoute('admin_comments_edit', array(
                'id' => $id
            ));
        }
        return $this->render('admin/comments/comments-edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
