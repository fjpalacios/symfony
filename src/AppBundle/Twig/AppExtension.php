<?php

namespace AppBundle\Twig;

use AppBundle\Utils\Markdown;
use Symfony\Component\Intl\Intl;

class AppExtension extends \Twig_Extension
{
    private $locales;
    private $parser;

    public function __construct($locales, Markdown $parser)
    {
        $this->locales = $locales;
        $this->parser = $parser;
    }

    public function getFunctions()
    {
        return [
                new \Twig_SimpleFunction('locales', [$this, 'getLocales']),
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
}
