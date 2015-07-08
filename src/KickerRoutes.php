<?php

class KickerRoutes {
    /**
     * Routes
     *
     * @access protected
     * @var array
     */
    protected static $routes = array();

    /**
     * Generic Routes
     * A List of generic routes and such
     *
     * @access protected
     * @var array
     */
    protected static $genericRoutes = array();

    /**
     * Named Routes
     * A List of named routes
     *
     * @access protected
     * @var array
     */
    protected static $namedRoutes = array();

    /**
     * Groups
     * Grouped routes and such
     *
     * @access protected
     * @var array
     */
    protected static $groups = array();

    /**
     * Map
     * Our main workhorse
     *
     * @access public
     * @param  closure  $callback
     * @param  array   $existing_routes
     * @param  boolean $return_ci_style_routes
     * @return array
     */
    public static function map($callback, $existing_routes = array(), $return_ci_style_routes = false) {
        $routeBuilder = new RouteBuilder();

        call_user_func_array($callback, array($routeBuilder));

        if (count($existing_routes) > 0) {
            foreach ($existing_routes as $to => $from) {
                $routeBuilder->genericRoute($to, $from);
            }
        }

        self::$routes = $routeBuilder->getRoutes();
        self::$genericRoutes = $routeBuilder->getGenericRoutes();

        $return_routes = array();

        if ($return_ci_style_routes === true) {
            foreach (self::$routes as $bob_route) {
                $return_routes[$bob_route['TO']][$bob_route['VERB']] = $bob_route['FROM'];
            }
        } else {
            if (PHP_SAPI === 'cli' OR defined('STDIN')) {
                //lets find all the CLI routes
                foreach (self::$routes as $bob_route) {
                    if ($bob_route['VERB'] == 'CLI') {
                        $return_routes[$bob_route['TO']] = $bob_route['FROM'];
                    }
                }
            } elseif (isset($_SERVER['REQUEST_METHOD'])) {
                //return only the verb that is being used
                foreach (self::$routes as $bob_route) {
                    if ($bob_route['VERB'] == $_SERVER['REQUEST_METHOD']) {
                        $return_routes[$bob_route['TO']] = $bob_route['FROM'];
                    }
                }
            } else {
                //map all the routes to the CI methodalogy
                foreach (self::$routes as $bob_route) {
                    $return_routes[$bob_route['TO']][$bob_route['VERB']] = $bob_route['FROM'];
                }
            }
        }

        foreach (self::$routes as $route_me) {
            if (!is_null($route_me['NAME'])) {
                self::$namedRoutes[$route_me['VERB']][$route_me['NAME']] = $route_me['TO'];
            }

            if (count($route_me['GROUPS']) > 0) {
                foreach ($route_me['GROUPS'] as $group) {
                    $x = $route_me;
                    unset($x['GROUPS']);
                    self::$groups[$group][] = $x;
                }
            }
        }

        return self::$namedRoutes;
        return array_merge($return_routes, self::$genericRoutes);
    }

    /**
     * URL
     * "resolve" a named route
     *
     * @access public
     * @param  string $name
     * @param  array  $parameters
     * @param  string $verb
     * @return array
     */
    public static function url($name, $parameters = array(), $verb = null) {
        if (is_null($verb)) {
            if (PHP_SAPI === 'cli' OR defined('STDIN')) {
                $verb = 'CLI';
            } elseif (isset($_SERVER['REQUEST_METHOD'])) {
                $verb = $_SERVER['REQUEST_METHOD'];
            } else {
                $verb = 'UNKNOWN';
            }
        } else {
            $verb = strtoupper($verb);
        }

        if (isset(self::$namedRoutes[$verb][$name])) {
            $route = self::$namedRoutes[$verb][$name];

            $route_pieces = explode('/', $route);

            if (count($route_pieces) == 1) {
                return $route;
            }

            if (count($parameters) > 0) {
                $loop = min(count($route_pieces), count($parameters));

                for ($a = 1; $a < $loop; $a++) {
                    $b = $a - 1;
                    $route_pieces[$a] = $parameters[$b];
                }
            }

            return implode('/', $route_pieces);
        } else {
            return '#';
        }
    }

    /**
     * Get Groups
     * Returns all grouped routes
     *
     * @access public
     * @return array
     */
    public static function getGroups() {
        return self::$groups;
    }

    /**
     * Get Group
     * Returns a specific group
     *
     * @access public
     * @param  string $name
     * @return array
     */
    public static function getGroup($name) {
        if (isset(self::$groups[$name])) {
            return self::$groups[$name];
        }

        return array();
    }
}
