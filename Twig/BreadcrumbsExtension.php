<?php
/**
 * Author: Paul Seleznev
 * Date: 1/07/2013
 */

namespace Frieser\BreadcrumbsBundle\Twig;

use Frieser\BreadcrumbsBundle\Builder\MainBuilder;

class BreadcrumbsExtension extends \Twig_Extension
{
    private $builder;

    public function __construct(MainBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function getFunctions()
    {
        return array(
            'build_breadcrumbs' => new \Twig_Function_Method($this, 'buildBreadcrumbs')
        );
    }

    public function buildBreadcrumbs()
    {
        return $this->builder->build();
    }

    public function getName()
    {
        return 'frieser_breadcrumbs.twig_extension';
    }
}