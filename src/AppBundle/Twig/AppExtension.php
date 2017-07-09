<?php

namespace AppBundle\Twig;

use AppBundle\Utils\Markdown;
use Symfony\Component\Intl\Intl;

class AppExtension extends \Twig_Extension
{
    /**
     * @var array
     */
    private $locales;

    public function __construct($locales)
    {
        $this->locales = $locales;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('locales', [$this, 'getLocales']),
        ];
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.).
     *
     * @return array
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
}
