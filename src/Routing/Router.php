<?php

namespace Parable\Routing;

class Router
{
    /** @var Route[] */
    protected $routes = [];

    /**
     * Add a Route object to the routes list as $name.
     *
     * @param string $name
     * @param Route  $route
     *
     * @return $this
     */
    public function addRoute($name, Route $route)
    {
        $route->checkValidProperties();

        $this->routes[$name] = $route;
        return $this;
    }

    /**
     * Add an array of routes to the routes list, where key is the route name.
     *
     * @param Route[] $routes
     *
     * @return $this
     */
    public function addRoutes(array $routes)
    {
        foreach ($routes as $name => $route) {
            $this->addRoute($name, $route);
        }
        return $this;
    }

    /**
     * Add a route to the routes list from array data.
     *
     * @param string $name
     * @param array $routeArray
     *
     * @return $this
     */
    public function addRouteFromArray($name, array $routeArray)
    {
        $route = Route::createFromDataArray($routeArray);
        $route->setName($name);

        $this->addRoute($name, $route);

        return $this;
    }

    /**
     * Add an array of routes defined by array data to the router.
     *
     * @param array $routes
     *
     * @return $this
     */
    public function addRoutesFromArray(array $routes)
    {
        foreach ($routes as $name => $route) {
            $this->addRouteFromArray($name, $route);
        }
        return $this;
    }

    /**
     * Return all routes currently set.
     *
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Return a route by its name.
     *
     * @param string $name
     *
     * @return Route|null
     */
    public function getRouteByName($name)
    {
        if (!isset($this->routes[$name])) {
            return null;
        }
        return $this->routes[$name];
    }

    /**
     * Try to find a match in all available routes.
     *
     * @param string $url
     *
     * @return Route|null
     */
    public function matchUrl($url)
    {
        $url = '/' . ltrim($url, '/');
        $url = $this->sanitizeUrl($url);

        if ($url && $route = $this->matchUrlDirectly($url)) {
            return $route;
        }
        if ($url && $route = $this->matchUrlWithParameters($url)) {
            return $route;
        }
        return null;
    }

    /**
     * Loop through routes and try to match directly.
     *
     * @param string $url
     *
     * @return Route|null
     */
    protected function matchUrlDirectly($url)
    {
        foreach ($this->routes as $route) {
            if ($route->matchDirectly($url)) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Loop through routes and try to match with parameters.
     *
     * @param string $url
     *
     * @return Route|null
     */
    protected function matchUrlWithParameters($url)
    {
        foreach ($this->routes as $route) {
            if ($route->matchWithParameters($url)) {
                return $route;
            }
        }
        return null;
    }

    /**
     * Return a url based on the $name provided, with $parameters passed (as [key => value]).
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    public function getRouteUrlByName($name, array $parameters = [])
    {
        $route = $this->getRouteByName($name);
        if (!$route) {
            return null;
        }
        return $route->buildUrlWithParameters($parameters);
    }

    /**
     * Sanitize the Url, removing html characters and other special characters.
     *
     * @param string $url
     *
     * @return false|string
     */
    protected function sanitizeUrl($url)
    {
        return filter_var($url, FILTER_SANITIZE_STRING);
    }
}
