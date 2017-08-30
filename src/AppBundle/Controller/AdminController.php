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
    public function postsAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Post');
        $posts = $repository->findAll();
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
            $userId = $this->getUser()->getId();
            $slug = new Slugify();
            $post->setAuthor($userId);
            $post->setDate(new \DateTime('now'));
            $post->setModDate(new \DateTime('now'));
            $post->setSlug($slug->slugify($post->getTitleEs()));
            $post->setType('post');
            $post->setCommentCount(0);
            $post->setViews(0);
            $em->persist($post);
            $flush = $em->flush();
            if (!$flush) {
                $status = 'POST_ADDED_PROPERLY';
            } else {
                $status = 'POST_ADDED_ERROR';
            }
            $this->session->getFlashBag()->add('status', $status);
        }
        return $this->render('admin/posts-add.html.twig', array(
                'form' => $form->createView()));
    }

    /**
     * @Route("/posts/del/{id}", name="admin_posts_del")
     */
    public function postsRemoveAction(Request $request, $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'ONLY_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $postRepo = $em->getRepository('AppBundle:Post');
        $post = $postRepo->find($id);
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
}
