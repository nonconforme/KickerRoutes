<?php

class RouteBuilder {
    /**
     * Routes
     * An array of routes
     *
     * @access protected
     * @var array
     */
    protected $routes = array();

    /**
     * Verbs
     * A list of used/available HTTP/CLI
     * verbs
     *
     * @access protected
     * @var array
     */
    protected $verbs = array(
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
        'HEAD',
        'OPTIONS',
        'CLI',
    );

    /**
     * Generic Routes
     * This routes are the normal CI style routes
     *
     * @access protected
     * @var array
     */
    protected $genericRoutes = array(
    );

    /**
     * Verb Collections
     * A list of "fake" verbs and the collection
     * of real http verbs that are used
     *
     * @access protected
     * @var array
     */
    protected $verbCollections = array(
    );

    /**
     * Get Routes
     * Returns the routes array
     *
     * @access public
     * @return array
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Set Routes
     * This should never be used but it will
     * allow you to set a predefined route array
     *
     * @access public
     * @param RouteBuilder
     */
    public function setRoutes($routes) {
        $this->routes = $routes;

        return $this;
    }

    /**
     * Get Verbs
     * Returns the verbs list
     *
     * @access public
     * @return array
     */
    public function getVerbs() {
        return $this->verbs;
    }

    /**
     * Set Verbs
     * Set the verbs list to something
     * very custom
     *
     * @access public
     * @param array $verbs
     * @return RouteBuilder
     */
    public function setVerbs($verbs) {
        $this->verbs = $verbs;

        return $this;
    }

    /**
     * Add Verb
     * Add a verb to the verbs list
     *
     * @access public
     * @param string $verb
     * @return RouteBuilder
     */
    public function addVerb($verb) {
        $verb = strtoupper($verb);

        if (!in_array($verb, $this->verbs)) {
            $this->verbs[] = $verb;
        }

        return $this;
    }

    /**
     * Get Verb Collections
     * Returns all the verb collections
     *
     * @access public
     * @return array
     */
    public function getVerbCollections() {
        return $this->verbCollections;
    }

    /**
     * Add Verb Collection
     * Allows a developer to add a verb collection
     *
     * @acces public
     * @param string $name
     * @param array $verbs
     * @return RouteBuilder
     */
    public function addVerbCollection($name, $verbs) {
        if (in_array(strtoupper($name), $this->verbs)) {
            throw new \ErrorException('Verb collections cannot use a HTTP verb as a name.');
            exit;
        }

        $this->verbCollections[$name] = $verbs;

        return $this;
    }

    /**
     * Execute a verb Collection
     * This allows a developer to execute a verb collection
     *
     * @access public
     * @param  string $collection
     * @param  string $to
     * @param  string $from
     * @param  string $name
     * @param  string|array $groups
     * @return RouteBuilder
     */
    public function executeVerbCollection($collection, $to, $from, $name = null, $groups = null) {
        if (isset($this->verbCollections[$collection])) {
            foreach ($this->verbCollections[$collection] as $verb) {
                $this->route($verb, $to, $from, $name, $groups);
            }
        }

        return $this;
    }

    /**
     * Generic Routes
     * Generic routes is the way KickerRoutes
     * handles real CI routes
     *
     * @access public
     * @param  string $to
     * @param  string $from
     * @return RouteBuilder
     */
    public function genericRoute($to, $from) {
        $this->genericRoutes[$to] = $from;

        return $this;
    }

    /**
     * Get Generic Routes
     * Returns an array of all generic routes
     *
     * @access public
     * @return array
     */
    public function getGenericRoutes() {
        return $this->genericRoutes;
    }

    /**
     * Route
     * The basic command to create a route
     *
     * @access public
     * @param  string $verb
     * @param  string $to
     * @param  string $from
     * @param  string $name
     * @param  string|array $groups
     * @return RouteBuilder
     */
    public function route($verb, $to, $from, $name = null, $groups = null) {
        if (is_string($groups)) {
            $x = $groups;
            unset($groups);
            $groups[] = $x;
            unset($x);
        } elseif (is_null($groups)) {
            $groups = array();
        } elseif (!is_array($groups)) {
            throw new \ErrorException('The groups parameter must be of datatype string or array.');
            exit;
        }

        if (!empty($verb)) {
            $verb = strtoupper($verb);
            $this->addVerb($verb);
        }

        $this->routes[] = array(
            'VERB' => $verb,
            'TO' => $to,
            'FROM' => $from,
            'NAME' => $name,
            'GROUPS' => $groups,
        );

        return $this;
    }

    /**
     * Any
     * This allows a route to be used for any http verb
     *
     * @access public
     * @param  string $to
     * @param  string $from
     * @param  string $name
     * @param  string|array $groups
     * @return RouteBuilder
     */
    public function any($to, $from, $name = null, $groups = null) {
        foreach ($this->verbs as $verb) {
            $this->route($verb, $to, $from, $name, $groups);
        }

        return $this;
    }

    /**
     * Magic Method: __call
     * Under the hood magic method... it allows us to
     * make it feel like there are a boat load of methods in this
     * class when in fact there aren't
     *
     * @access public
     * @param  string $method
     * @param  array  $arguments
     */
    public function __call($method, $arguments = array()) {
        array_unshift($arguments, $method);
        if (in_array($method, array_keys($this->verbCollections))) {
            call_user_func_array(array($this, 'executeVerbCollection'), $arguments);
        } else {
            call_user_func_array(array($this, 'route'), $arguments);
        }

        return $this;
    }
}
