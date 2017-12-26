<?php

namespace AppBundle\Twig;

use AppBundle\AppBundle;
use AppBundle\Utils\Markdown;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Intl\Intl;

class AppExtension extends \Twig_Extension
{
    private $locales;
    private $parser;
    private $doctrine;

    public function __construct($locales, Markdown $parser, ManagerRegistry $doctrine)
    {
        $this->locales = $locales;
        $this->parser = $parser;
        $this->doctrine = $doctrine;
    }

    public function getFunctions()
    {
        return [
                new \Twig_SimpleFunction('locales', [$this, 'getLocales']),
                new \Twig_SimpleFunction('has_content', [$this, 'userHasPublishedContent']),
                new \Twig_SimpleFunction('has_posts', [$this, 'categoryHasPosts']),
                new \Twig_SimpleFunction('has_courses', [$this, 'courseHasPosts']),
                new \Twig_SimpleFunction('course_slug', [$this, 'courseSlug']),
                new \Twig_SimpleFunction('course_name', [$this, 'courseName']),
                new \Twig_SimpleFunction('course_anterior', [$this, 'courseAnteriorArticle']),
                new \Twig_SimpleFunction('course_posterior', [$this, 'coursePosteriorArticle']),
        ];
    }

    public function getFilters()
    {
        return array(
                new \Twig_SimpleFilter(
                        'md2html',
                        array($this, 'markdownToHtml'),
                        array('is_safe' => array('html'), 'pre_escape' => 'html')
                ),
        );
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.).
     */
    public function getLocales()
    {
        $localeCodes = explode('|', $this->locales);
        $locales = [];
        foreach ($localeCodes as $localeCode) {
            $locales[] = ['code' => $localeCode, 'name' => Intl::getLocaleBundle()->getLocaleName($localeCode, $localeCode)];
        }
        return $locales;
    }

    /*
     * Transforms the given Markdown content into HTML content.
     */
    public function markdownToHtml($content)
    {
        return $this->parser->toHtml($content);
    }

    /*
     * Checks if a given user id has (or not) published content
     */
    public function userHasPublishedContent($id)
    {
        $postRepo = $this->doctrine->getRepository('AppBundle:Post');
        $hasContent = $postRepo->findOneBy(array('author' => $id));
        if ($hasContent) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Checks if a given category id has (or not) published posts
     */
    public function categoryHasPosts($id)
    {
        $postRepo = $this->doctrine->getRepository('AppBundle:Post');
        $hasContent = $postRepo->findOneBy(array(
                'category' => $id,
                'status' => 'publish'
            )
        );
        if ($hasContent) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Checks if a given course id has (or not) published posts
     */
    public function courseHasPosts($id)
    {
        $postRepo = $this->doctrine->getRepository('AppBundle:Post');
        $hasContent = $postRepo->findOneBy(array(
                'course' => $id,
                'status' => 'publish'
            )
        );
        if ($hasContent) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Returns the name of the course in Spanish or English depends on current locale
     */
    public function courseName($id, $locale)
    {
        $courseRepo = $this->doctrine->getRepository('AppBundle:Course');
        $course = $courseRepo->findOneBy(array('id' => $id));
        if ($locale == 'en') {
            return $course->getNameEn();
        }
        return $course->getNameEs();
    }

    /*
     * Returns the slug of the course
     */
    public function courseSlug($id)
    {
        $courseRepo = $this->doctrine->getRepository('AppBundle:Course');
        $course = $courseRepo->findOneBy(array('id' => $id));
        return $course->getSlug();
    }

    /*
     * Returns the previous course's article required data or null if none exists
     */
    public function courseAnteriorArticle($id, $course)
    {
        $postRepo = $this->doctrine->getRepository('AppBundle:Post');
        $article = $postRepo->findOneBy(array(
                'course' => $course,
                'id' => $id-1,
                'status' => 'publish'
            )
        );
        return $article ? [$article->getTitleEs(), $article->getTitleEn(), $article->getSlug()] : null;
    }

    /*
     * Returns the next course's article required data or null if none exists
     */
    public function coursePosteriorArticle($id, $course)
    {
        $postRepo = $this->doctrine->getRepository('AppBundle:Post');
        $article = $postRepo->findOneBy(array(
                'course' => $course,
                'id' => $id+1,
                'status' => 'publish'
            )
        );
        return $article ? [$article->getTitleEs(), $article->getTitleEn(), $article->getSlug()] : null;
    }
}
