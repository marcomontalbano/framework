<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\adapters;

use \mako\database\Connection;

/**
 * Database adapter.
 *
 * @author  Frederic G. Østby
 */

class Database implements \mako\cache\adapters\AdapterInterface
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Database connection
	 * 
	 * @var \mako\database\Connection
	 */

	protected $connection;

	/**
	 * Database table.
	 * 
	 * @var string
	 */

	protected $table;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\database\Connection  $connection  Database connection
	 * @param   string                     $table       Database table
	 */

	public function __construct(Connection $connection, $table)
	{
		$this->connection = $connection;

		$this->table = $table;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Returns a query builder instance.
	 *
	 * @access  protected
	 * @return  \mako\database\query\Query
	 */

	protected function table()
	{
		return $this->connection->table($this->table);
	}

	/**
	 * Store data in the cache.
	 *
	 * @access  public
	 * @param   string   $key    Cache key
	 * @param   mixed    $data   The data to store
	 * @param   int      $ttl    (optional) Time to live
	 * @return  boolean
	 */

	public function write($key, $data, $ttl = 0)
	{
		$ttl = (((int) $ttl === 0) ? 31556926 : (int) $ttl) + time();
		
		$this->delete($key);

		return $this->table()->insert(['key' => $key, 'data' => serialize($data), 'lifetime' => $ttl]);
	}

	/**
	 * Returns TRUE if the cache key exists and FALSE if not.
	 * 
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */

	public function has($key)
	{
		return (bool) $this->table()->where('key', '=', $key)->where('lifetime', '>', time())->count();
	}

	/**
	 * Fetch data from the cache.
	 *
	 * @access  public
	 * @param   string  $key  Cache key
	 * @return  mixed
	 */
	
	public function read($key)
	{
		$cache = $this->table()->where('key', '=', $key)->first();

		if($cache !== false)
		{
			if(time() < $cache->lifetime)
			{
				return unserialize($cache->data);
			}
			
			$this->delete($key);
		}
		
		return false;
	}

	/**
	 * Delete data from the cache.
	 *
	 * @access  public
	 * @param   string   $key  Cache key
	 * @return  boolean
	 */
	
	public function delete($key)
	{
		return (bool) $this->table()->where('key', '=', $key)->delete();
	}

	/**
	 * Clears the user cache.
	 *
	 * @access  public
	 * @return  boolean
	 */
	
	public function clear()
	{
		$this->table()->delete();
											
		return true;
	}
}