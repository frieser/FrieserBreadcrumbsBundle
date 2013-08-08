<?php
/**
 * Author: Paul Seleznev
 * Date: 4/08/2013
 */
namespace Art\BreadcrumbsBundle\Builder;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

class YMLBuilder implements BuilderInterface
{
    private $path;
    private $router;
    private $request;
    private $routeCollection;
    private $schema;

    public function __construct(RouterInterface $router, $schema)
    {
        $this->router = $router;
        $this->schema = $schema;
    }

    public function build()
    {
        $path = realpath($this->schema);
        $this->routeCollection = $this->router->getRouteCollection();
        if (!is_string($path) || 'yml' !== pathinfo($path, PATHINFO_EXTENSION)) {
            throw new \Exception("The schema file provided isn't correct");
        }
        $structure = Yaml::parse($path);

        if (!$this->request) {
            throw new \Exception('This function should be user only in request scope');
        }
        
        $this->routeCollection = $this->router->getRouteCollection();
        $breadcrumbs = $this->createBreadcrumbs($this->request, $structure);

        return $breadcrumbs;
    }

    public function createBreadcrumbs($request, $structure)
    {
        $breadcrumbs = array();
        if (!isset($structure['route'])) {
            return;
        }
        if ($structure['route'] === $request->get('_route')) {
            $breadcrumbs[] = array(
                'label' => $structure['label']
            );

            return $breadcrumbs;
        }
        if (!isset($structure['children'])) {
            return null;
        }
        foreach ($structure['children'] as $child) {
            $result = $this->createBreadcrumbs($request, $child);
            if ($result) {
                $route = $this->routeCollection->get($structure['route']);
                $paramVariables = $route->compile()->getVariables();
                if (!empty($paramVariables)) {
                    $parameters = array();
                    foreach ($paramVariables as $variable) {
                        $value = $request->attributes->get($variable);
                        if (isset($value)) {
                            $parameters[$variable] = $value;
                        }
                    }
                    $breadcrumbs[] = array(
                        'label' => $structure['label'],
                        'url' => $this->router->generate($structure['route'], $parameters)
                    );
                } else {
                    $breadcrumbs[] = array(
                        'label' => $structure['label'],
                        'url' => $this->router->generate($structure['route'])
                    );
                }

                return array_merge($breadcrumbs, $result);
            }
        }

        return $breadcrumbs;
    }

    public function setRequest(Request $request = null)
    {
        $this->request = $request;

        return $this;
    }
}
