<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SitemapController extends Controller
{
    /**
     * @Route("/sitemap.xml", name="sitemap")
     */
    public function sitemapAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle:Post')
            ->findBy(array(
                'status' => 'publish',
                'type' => 'post'), array(
                'modDate' => 'DESC'
            ));
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();
        $urls[] = array(
            'loc' => '/sitemap-en.xml',
            'modDate' => date('r'),
            'changefreq' => 'daily',
            'priority' => '0.5'
        );
        $urls[] = array(
            'loc' => '/sitemap-es.xml',
            'modDate' => date('r'),
            'changefreq' => 'daily',
            'priority' => '0.5'
        );
        return $this->render('public/sitemap-index.xml.twig', array(
            'urls' => $urls,
            'hostname' => $hostname,
            'posts' => $posts
        ));
    }

    /**
     * @Route("/sitemap-en.xml", name="sitemap_en")
     */
    public function sitemapEnAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();
        $posts = $em->getRepository('AppBundle:Post')
            ->findBy(array(
                'status' => 'publish',
                'type' => 'post'), array(
                'modDate' => 'DESC'
            ));
        foreach ($posts as $post) {
            $urls[] = array(
                'loc' => $this->get('router')->generate('post', array(
                    'slug' => $post->getSlug(),
                    '_locale' => 'en'
                )),
                'modDate' => $post->getModDate(),
                'changefreq' => 'daily',
                'priority' => '0.5'
            );
        }
        $urls[] = array(
            'loc' => $this->get('router')->generate('home'),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        );
        $urls[] = array(
            'loc' => $this->get('router')->generate('about_us'),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        );
        $urls[] = array(
            'loc' => $this->get('router')->generate('categories'),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        );
        $urls[] = array(
            'loc' => $this->get('router')->generate('contact'),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        );
        return $this->render('public/sitemap.xml.twig', array(
            'urls' => $urls,
            'hostname' => $hostname
        ));
    }

    /**
     * @Route("/sitemap-es.xml", name="sitemap_es")
     */
    public function sitemapEsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $urls = array();
        $hostname = $request->getSchemeAndHttpHost();
        $posts = $em->getRepository('AppBundle:Post')
            ->findBy(array(
                'status' => 'publish',
                'type' => 'post'), array(
                'modDate' => 'DESC'
            ));
        foreach ($posts as $post) {
            $urls[] = array(
                'loc' => $this->get('router')->generate('post', array(
                    'slug' => $post->getSlug(),
                    '_locale' => 'es'
                )),
                'modDate' => $post->getModDate(),
                'changefreq' => 'daily',
                'priority' => '0.5'
            );
        }
        $urls[] = array(
            'loc' => $this->get('router')->generate('home'),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        );
        $urls[] = array(
            'loc' => $this->get('router')->generate('about_us'),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        );
        $urls[] = array(
            'loc' => $this->get('router')->generate('categories'),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        );
        $urls[] = array(
            'loc' => $this->get('router')->generate('contact'),
            'changefreq' => 'weekly',
            'priority' => '1.0'
        );
        return $this->render('public/sitemap.xml.twig', array(
            'urls' => $urls,
            'hostname' => $hostname
        ));
    }
}
