<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\FixturesTrait;
use AppBundle\Entity\Post;
use Cocur\Slugify\Slugify;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class PostFixtures extends AbstractFixture implements DependentFixtureInterface,
    ContainerAwareInterface
{
    use ContainerAwareTrait;
    use FixturesTrait;

    public function load(ObjectManager $manager)
    {
        foreach ($this->getRandomPostTitle() as $i => $title) {
            $post = new Post();
            $slug = new Slugify();
            $post->setTitleEs($title);
            $post->setTitleEn($title);
            $post->setExcerptEs($this->getRandomPostExcerpt());
            $post->setExcerptEn($this->getRandomPostExcerpt());
            $post->setContentEs($this->getPostContent());
            $post->setContentEn($this->getPostContent());
            $post->setDate(new \DateTime('now - ' . $i . 'days'));
            $post->setModDate(new \DateTime('now - ' . $i . 'days'));
            $post->setSlug($slug->slugify($post->getTitleEs()));
            $post->setType($this->getRandomType());
            $post->setNavbar(0);
            $post->setCommentStatus('open');
            $post->setCommentCount(0);
            $post->setViews(0);
            $post->setAuthor(0 === $i ? $this->getReference('super-admin') : $this->getRandomUser());
            $post->setStatus($this->getRandomStatus());
            $manager->persist($post);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }

    private function getRandomType()
    {
        $status = ['post', 'page'];
        $index = array_rand($status);
        return $status[$index];
    }

    private function getRandomUser()
    {
        $admins = ['super-admin', 'admin'];
        $index = array_rand($admins);
        return $this->getReference($admins[$index]);
    }

    private function getRandomStatus()
    {
        $status = ['publish', 'draft'];
        $index = array_rand($status);
        return $status[$index];
    }
}
