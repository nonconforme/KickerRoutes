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
	 * Names
	 *
	 * @access protected
	 * @var array
	 */
	protected static $names = array();

	/**
	 * Map
	 * This handles calling the Routebuilder and such
	 *
	 * @access public
	 * @param  Closure $callback        [description]
	 * @param  array  $existing_routes [description]
	 */
	public static function map($callback, $existing_routes = array()) {
		$reservedRoutes = array(
			'default_controller',
			'404_override',
			'translate_uri_dashes',
		);

		$routeBuilder = new RouteBuilder();

		call_user_func_array($callback, array($routeBuilder));

		if (count($existing_routes) > 0) {
			self::$original_routes = $existing_routes;

			foreach ($existing_routes as $to => $from) {
				if (in_array($to, $reservedRoutes)) {
					continue;
				}

				if (!is_array($from)) {
					$routeBuilder->ALL($to, $from);
				} else {
					$keys = array_keys($from);

					$routeBuilder->route(strtoupper($keys[0]), $to, $from[$keys[0]]);
				}

				unset($existing_routes[$to]);
			}
		}

		self::$routes = $routeBuilder->getRoutes();
		self::$names = $routeBuilder->getNames();

		if (isset($_SERVER['REQUEST_METHOD'])) {
			if (isset(self::$routes[$_SERVER['REQUEST_METHOD']])) {
				return array_merge($existing_routes, self::$routes[$_SERVER['REQUEST_METHOD']]);
			} else {
				return $existing_routes;
			}
		} else {
			return $existing_routes;
		}
	}

	/**
	 * Get Routes
	 * Returns all the routes
	 *
	 * @access public
	 * @return array
	 */
	public static function getRoutes() {
		return self::$routes;
	}

	/**
	 * Get Names
	 * Returns route names
	 *
	 * @access public
	 * @return array
	 */
	public static function getNames() {
		return self::$names;
	}

	/**
	 * URL
	 * Get a named route and add any variables
	 *
	 * @access public
	 * @param  string $name
	 * @param  array  $values
	 * @param  string $method
	 * @return string
	 */
	public static function url($name, $values = array(), $method = null) {
		if (is_null($method)) {
			(isset($_SERVER['REQUEST_METHOD'])) ? $_SERVER['REQUEST_METHOD'] : 'UNKNOWN';
		}

		if (isset(self::$names[strtoupper($method)][$name])) {
			$route = self::$names[strtoupper($method)][$name];

			$route_pieces = explode('/', $route);

			if (count($route_pieces) == 1) {
				return $route;
			}

			if (count($values) > 0) {
				$values_counter = 0;
				for ($a = 1; $a < count($route_pieces); $a++) {
					$route_pieces[$a] = $values[$values_counter];

					$values_counter++;
				}
			}

			return implode('/', $route_pieces);
		}
	}
}