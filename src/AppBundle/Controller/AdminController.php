<?php

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Route("/{_locale}", requirements={"_locale" = "%app.locales%"})
 */
class AdminController extends Controller
{
    private $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function adminAction(Request $request, UserPasswordEncoderInterface
        $passwordEncoder)
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

}
