<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\core\services;

use \mako\redis\ConnectionManager;

/**
 * Redis service.
 *
 * @author  Frederic G. Østby
 */

class RedisService extends \mako\core\services\Service
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	// Nothing here

	//---------------------------------------------
	// Class methods
	//---------------------------------------------
	
	/**
	 * Registers the service.
	 * 
	 * @access  public
	 */

	public function register()
	{
		$this->container->registerSingleton(['mako\redis\ConnectionManager', 'redis'], function($container)
		{
			$config = $container->get('config')->get('redis');

			return new ConnectionManager($config['default'], $config['configurations']);
		});
	}
}