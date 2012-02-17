<?php
namespace Kitpages\UserGeneratedBundle\Twig\Extension;

class RatingExtension extends \Twig_Extension
{

    public static function round($value, $precision = 2) {
        return round($value, $precision);
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            'kit_usergenerate_round' => new \Twig_Filter_Function('Kitpages\UserGeneratedBundle\Twig\Extension\RatingExtension::round'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'kit_usergenerate';
    }
}
