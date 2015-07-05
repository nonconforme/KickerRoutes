<?php

class RouteBuilder {
	/**
	 * Routes
	 * A Compiled list of routes
	 *
	 * @access protected
	 * @var array
	 */
	protected $routes = array();

	/**
	 * Names
	 * Lets have the ability to name our routes
	 *
	 * @access protected
	 * @var array
	 */
	protected $names = array();

	/**
	 * Verbs
	 * A list of available http verbs
	 *
	 * @access protected
	 * @var array
	 */
	protected $verbs = array();

	/**
	 * Collections
	 * Collections are a group of verbs
	 *
	 * @access protected
	 * @var array
	 */
	protected $collections = array();

	/**
	 * Class Construct
	 * Setup of some basic stuff
	 *
	 * @access public
	 */
	public function __construct() {
		$this->verbs = array(
			'GET',
			'POST',
			'PUT',
			'DELETE',
			'PATCH',
			'HEAD',
			'OPTIONS',
		);
	}

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
	 * Get Names
	 * Returns the named routes
	 *
	 * @access public
	 * @return array
	 */
	public function getNames() {
		return $this->names;
	}

	/**
	 * Verbs
	 * Returns the listed verbs
	 *
	 * @access public
	 * @return array
	 */
	public function getVerbs() {
		return $this->verbs;
	}

	/**
	 * Route
	 * Define a generic route
	 *
	 * @access public
	 * @param  string $method
	 * @param  string $to
	 * @param  string $from
	 *
	 * @return RouteBuilder
	 */
	public function route($verb, $to, $from, $name = null) {
		if (!in_array(strtoupper($verb), $this->verbs)) {
			$this->verbs[] = strtoupper($verb);
		}

		$this->routes[strtoupper($verb)][$to] = $from;

		if (!is_null($name)) {
			$this->names[strtoupper($verb)][$name] = $to;
		}

		return $this;
	}

	/**
	 * All Route
	 * Defines a route for all listed verbs
	 *
	 * @access public
	 * @param string $to
	 * @param string $from
	 *
	 * @return RouteBuilder
	 */
	public function ALL($to, $from, $name = null) {
		foreach ($this->verbs as $verb) {
			$this->route($verb, $to, $from, $name);
		}

		return $this;
	}

	/**
	 * Verb Collections
	 * Alias a collection of verbs togther
	 *
	 * @access public
	 * @param string $name
	 * @param array  $verbs
	 *
	 * @return RouteBuilder
	 */
	public function addCollection($name, $verbs = array()) {
		$this->collections[$name] = $verbs;

		return $this;
	}

	/**
	 * Collection
	 * Send a route to a verb collection
	 *
	 * @access public
	 * @param  string $col
	 * @param  string $to
	 * @param  string $from
	 * @param  string $name
	 *
	 * @return RouteBuilder
	 */
	public function collection($col, $to, $from, $name = null) {
		if (isset($this->collections[$col])) {
			foreach ($this->collections[$col] as $verb) {
				$this->route($verb, $to, $from, $name);
			}
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

		if (isset($this->collections[$method])) {
			call_user_func_array(array($this, 'collection'), $arguments);
		} else {
			call_user_func_array(array($this, 'route'), $arguments);
		}

		return $this;
	}
}