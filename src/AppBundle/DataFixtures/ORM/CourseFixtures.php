<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\FixturesTrait;
use AppBundle\Entity\Course;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class CourseFixtures extends AbstractFixture
{
    use FixturesTrait;

    public function load(ObjectManager $manager)
    {
        $course = new Course();
        $course->setNameEs('Lorem ipsum');
        $course->setNameEn('Lorem ipsum');
        $course->setSlug('lorem-ipsum');
        $course->setDescriptionEn($this->getRandomPostExcerpt());
        $course->setDescriptionEs($this->getRandomPostExcerpt());
        $manager->persist($course);
        $this->addReference('course', $course);
        $manager->flush();
    }
}
