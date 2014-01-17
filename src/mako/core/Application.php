<?php

namespace mako\core;

use \Closure;
use \LogicException;

use \mako\core\Config;
use \mako\core\errorhandler\ErrorHandler;
use \mako\http\Request;
use \mako\http\Response;
use \mako\http\routing\Dispatcher;
use \mako\http\routing\Router;
use \mako\http\routing\Routes;
use \mako\http\routing\URLBuilder;
use \mako\security\Signer;

/**
 * Application.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Application extends \mako\core\Syringe
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Singleton instance.
	 * 
	 * @var \mako\core\Application
	 */

	protected static $instance;

	/**
	 * Config instance.
	 * 
	 * @var \mako\core\Config
	 */

	protected $config;

	/**
	 * Application language.
	 * 
	 * @var string
	 */

	protected $language;

	/**
	 * Application path.
	 * 
	 * @var string
	 */

	protected $applicationPath;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   string  $applicationPath  Application path
	 */

	public function __construct($applicationPath)
	{
		$this->applicationPath = $applicationPath;

		$this->boot();
	}

	/**
	 * Starts the application and returns a singleton instance of the application.
	 * 
	 * @access  public
	 * @param   string                  $applicationPath  Application path
	 * @return  \mako\core\Application
	 */

	public static function start($applicationPath)
	{
		if(!empty(static::$instance))
		{
			throw new LogicException(vsprintf("%s(): The application has already been started.", [__METHOD__]));
		}

		return static::$instance = new static($applicationPath);
	}

	/**
	 * Returns a singleton instance of the application.
	 * 
	 * @access  public
	 * @return  \mako\core\Application
	 */

	public static function instance()
	{
		if(empty(static::$instance))
		{
			throw new LogicException(vsprintf("%s(): The application has not been started yet.", [__METHOD__]));
		}

		return static::$instance;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns the application language.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Sets the application language.
	 * 
	 * @access  public
	 * @param   string  $language  Application language
	 */

	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * Gets the application path.
	 * 
	 * @access  public
	 * @return  string
	 */

	public function getApplicationPath()
	{
		return $this->applicationPath;
	}

	/**
	 * Register classes in the dependency injection container.
	 * 
	 * @access  protected
	 */

	protected function registerClasses()
	{
		// Register self so that the application instance can be injected

		$this->registerInstance(['mako\core\Application', 'app'], $this);

		// Register error handler instance

		$this->registerInstance(['mako\core\errorhandler\ErrorHandler', 'errorhandler'], new ErrorHandler());

		// Register config instance

		$this->registerInstance(['mako\core\Config', 'config'], $this->config = new Config($this->applicationPath));

		// Register the signer class

		$this->registerSingleton(['mako\security\Signer', 'signer'], function()
		{
			return new Signer($this->config->get('application.secret'));
		});

		// Register the request class

		$this->registerSingleton(['mako\http\Request', 'request'], function()
		{
			return new Request(['languages' => $this->config->get('application.languages')], $this->get('signer'));
		});

		// Register the response class

		$this->registerSingleton(['mako\http\Response', 'response'], 'mako\http\Response');

		// Register the route collection

		$this->registerSingleton(['mako\http\routing\Routes', 'routes'], 'mako\http\routing\Routes');

		// Register the URL builder

		$this->registerSingleton(['mako\http\routing\URLBuilder', 'urlbuilder'], function()
		{
			return new URLBuilder($this->get('request'), $this->get('routes'), $this->config->get('application.clean_urls'));
		});
	}

	/**
	 * Loads the application bootstrap file.
	 * 
	 * @access  protected
	 */

	protected function bootstrap()
	{
		$bootstrap = function($app)
		{
			include $this->applicationPath . '/bootstrap.php';
		};

		$bootstrap($this);
	}

	/**
	 * Boots the application.
	 * 
	 * @access  protected
	 */

	protected function boot()
	{
		// Register classes in the dependency injection container

		$this->registerClasses();

		// Load the application bootstrap file

		$this->bootstrap();
	}

	/**
	 * Prepends an exception handler to the stack.
	 * 
	 * @access  public
	 * @param   string    $exception  Exception type
	 * @param   \Closure  $handler    Exception handler
	 */

	public function handle($exception, Closure $handler)
	{
		$this->get('errorhandler')->handle($exception, $handler);
	}

	/**
	 * Loads application routes.
	 * 
	 * @access  public
	 */

	public function loadRoutes()
	{
		$loader = function($app, $routes)
		{
			include $this->applicationPath . '/routes.php';
		};

		$loader($this, $this->get('routes'));
	}

	/**
	 * Dispatches the request and returns its response.
	 * 
	 * @access  public
	 * @return  \mako\http\Response
	 */

	protected function dispatch()
	{
		$request = $this->get('request');

		// Override the application language?

		if(($language = $request->language()) !== null)
		{
			$this->setLanguage($language);
		}

		// Load routes

		$this->loadRoutes();

		$routes = $this->get('routes');

		// Route the request

		$router = new Router($request, $routes);

		$route = $router->route();

		// Dispatch the request and return the response

		return (new Dispatcher($routes, $route, $request, $this->get('response'), $this))->dispatch();
	}

	/**
	 * Runs the application.
	 * 
	 * @access  public
	 */

	public function run()
	{
		ob_start();

		// Dispatch the request

		$this->dispatch()->send();
	}
}

/** -------------------- End of file -------------------- **/