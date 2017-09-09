<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UserFixtures extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function load(ObjectManager $manager)
    {
        $passwordEncoder = $this->container->get('security.password_encoder');
        $userSuperAdmin = new User();
        $password = $passwordEncoder->encodePassword($userSuperAdmin, '123456');
        $userSuperAdmin->setPassword($password);
        $userSuperAdmin->setDate(new \DateTime('now'));
        $userSuperAdmin->setUsername('alice');
        $userSuperAdmin->setName('Alice');
        $userSuperAdmin->setEmail('alice@test.com');
        $userSuperAdmin->setBio('Testing Bio');
        $userSuperAdmin->setUrl('hhtps://sargantanacode.es/');
        $userSuperAdmin->setRoles(array('ROLE_SUPER_ADMIN'));
        $manager->persist($userSuperAdmin);
        $this->addReference('super-admin', $userSuperAdmin);
        $userUser = new User();
        $password = $passwordEncoder->encodePassword($userUser, '123456');
        $userUser->setPassword($password);
        $userUser->setDate(new \DateTime('now'));
        $userUser->setUsername('bob');
        $userUser->setName('Bob');
        $userUser->setEmail('bob@test.com');
        $userUser->setBio('Testing Bio');
        $userUser->setUrl('hhtps://sargantanacode.es/');
        $userUser->setRoles(array('ROLE_USER'));
        $manager->persist($userUser);
        $this->addReference('user', $userUser);
        $userAdmin = new User();
        $password = $passwordEncoder->encodePassword($userAdmin, '123456');
        $userAdmin->setPassword($password);
        $userAdmin->setDate(new \DateTime('now'));
        $userAdmin->setUsername('charlie');
        $userAdmin->setName('Charlie');
        $userAdmin->setEmail('charlie@test.com');
        $userAdmin->setBio('Testing Bio');
        $userAdmin->setUrl('hhtps://sargantanacode.es/');
        $userAdmin->setRoles(array('ROLE_ADMIN'));
        $manager->persist($userAdmin);
        $this->addReference('admin', $userAdmin);
        $manager->flush();
    }
}
