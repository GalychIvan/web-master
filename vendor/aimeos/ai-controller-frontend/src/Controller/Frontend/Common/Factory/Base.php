<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2012
 * @copyright Aimeos (aimeos.org), 2015-2022
 * @package Controller
 * @subpackage Frontend
 */


namespace Aimeos\Controller\Frontend\Common\Factory;


/**
 * Common methods for all factories.
 *
 * @package Controller
 * @subpackage Frontend
 * @deprecated 2023.01
 */
class Base
{
	private static $objects = [];


	/**
	 * Injects a controller object.
	 * The object is returned via create() if an instance of the class
	 * with the name name is requested.
	 *
	 * @param string $classname Full name of the class for which the object should be returned
	 * @param \Aimeos\Controller\Frontend\Iface|null $controller Frontend controller object
	 */
	public static function injectController( string $classname, \Aimeos\Controller\Frontend\Iface $controller = null )
	{
		self::$objects[$classname] = $controller;
	}


	/**
	 * Adds the decorators to the controller object.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context instance with necessary objects
	 * @param \Aimeos\Controller\Frontend\Common\Iface $controller Controller object
	 * @param array $decorators List of decorator names that should be wrapped around the controller object
	 * @param string $classprefix Decorator class prefix, e.g. "\Aimeos\Controller\Frontend\Basket\Decorator\"
	 * @return \Aimeos\Controller\Frontend\Iface Controller object
	 */
	protected static function addDecorators( \Aimeos\MShop\ContextIface $context,
		\Aimeos\Controller\Frontend\Iface $controller, array $decorators, string $classprefix ) : \Aimeos\Controller\Frontend\Iface
	{
		foreach( $decorators as $name )
		{
			if( ctype_alnum( $name ) === false )
			{
				$classname = is_string( $name ) ? $classprefix . $name : '<not a string>';
				throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Invalid characters in class name "%1$s"', $classname ) );
			}

			$classname = $classprefix . $name;

			if( class_exists( $classname ) === false ) {
				throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Class "%1$s" not available', $classname ) );
			}

			$controller = new $classname( $controller, $context );

			\Aimeos\MW\Common\Base::checkClass( '\\Aimeos\\Controller\\Frontend\\Common\\Decorator\\Iface', $controller );
		}

		return $controller;
	}


	/**
	 * Adds the decorators to the controller object.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context instance with necessary objects
	 * @param \Aimeos\Controller\Frontend\Common\Iface $controller Controller object
	 * @param string $domain Domain name in lower case, e.g. "product"
	 * @return \Aimeos\Controller\Frontend\Iface Controller object
	 */
	protected static function addControllerDecorators( \Aimeos\MShop\ContextIface $context,
		\Aimeos\Controller\Frontend\Iface $controller, string $domain ) : \Aimeos\Controller\Frontend\Iface
	{
		if( !is_string( $domain ) || $domain === '' ) {
			throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Invalid domain "%1$s"', $domain ) );
		}

		$localClass = str_replace( '/', '\\', ucwords( $domain, '/' ) );
		$config = $context->config();

		/** controller/frontend/common/decorators/default
		 * Configures the list of decorators applied to all frontend controllers
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to configure a list of decorator names that should
		 * be wrapped around the original instance of all created controllers:
		 *
		 *  controller/frontend/common/decorators/default = array( 'decorator1', 'decorator2' )
		 *
		 * This would wrap the decorators named "decorator1" and "decorator2" around
		 * all controller instances in that order. The decorator classes would be
		 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator1" and
		 * "\Aimeos\Controller\Frontend\Common\Decorator\Decorator2".
		 *
		 * @param array List of decorator names
		 * @since 2014.03
		 * @category Developer
		 */
		$decorators = $config->get( 'controller/frontend/common/decorators/default', [] );
		$excludes = $config->get( 'controller/frontend/' . $domain . '/decorators/excludes', [] );

		foreach( $decorators as $key => $name )
		{
			if( in_array( $name, $excludes ) ) {
				unset( $decorators[$key] );
			}
		}

		$classprefix = '\\Aimeos\\Controller\\Frontend\\Common\\Decorator\\';
		$controller = self::addDecorators( $context, $controller, $decorators, $classprefix );

		$classprefix = '\\Aimeos\\Controller\\Frontend\\Common\\Decorator\\';
		$decorators = $config->get( 'controller/frontend/' . $domain . '/decorators/global', [] );
		$controller = self::addDecorators( $context, $controller, $decorators, $classprefix );

		$classprefix = '\\Aimeos\\Controller\\Frontend\\' . ucfirst( $localClass ) . '\\Decorator\\';
		$decorators = $config->get( 'controller/frontend/' . $domain . '/decorators/local', [] );
		$controller = self::addDecorators( $context, $controller, $decorators, $classprefix );

		return $controller->setObject( $controller );
	}


	/**
	 * Creates a controller object.
	 *
	 * @param \Aimeos\MShop\ContextIface $context Context instance with necessary objects
	 * @param string $classname Name of the controller class
	 * @param string $interface Name of the controller interface
	 * @return \Aimeos\Controller\Frontend\Iface Controller object
	 */
	protected static function createController( \Aimeos\MShop\ContextIface $context,
		string $classname, string $interface ) : \Aimeos\Controller\Frontend\Iface
	{
		if( isset( self::$objects[$classname] ) ) {
			return self::$objects[$classname];
		}

		if( class_exists( $classname ) === false ) {
			throw new \Aimeos\Controller\Frontend\Exception( sprintf( 'Class "%1$s" not available', $classname ) );
		}

		$controller = new $classname( $context );

		\Aimeos\MW\Common\Base::checkClass( $interface, $controller );

		return $controller;
	}

}
