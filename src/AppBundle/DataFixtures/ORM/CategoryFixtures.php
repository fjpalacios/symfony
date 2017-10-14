<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\FixturesTrait;
use AppBundle\Entity\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class CategoryFixtures extends AbstractFixture
{
    use FixturesTrait;

    public function load(ObjectManager $manager)
    {
        $category = new Category();
        $category->setNameEs('Lorem ipsum');
        $category->setNameEn('Lorem ipsum');
        $category->setSlug('lorem-ipsum');
        $category->setDescriptionEn($this->getRandomPostExcerpt());
        $category->setDescriptionEs($this->getRandomPostExcerpt());
        $manager->persist($category);
        $this->addReference('category', $category);
        $manager->flush();
    }
}
