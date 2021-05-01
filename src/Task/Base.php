<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021
 */


namespace Aimeos\Upscheme\Task;


/**
 * Base class for all setup tasks
 */
abstract class Base implements Iface
{
	private $up;


	/**
	 * Initializes the setup task object
	 *
	 * @param \Aimeos\Upscheme\Up $up Main Upscheme object
	 */
	public function __construct( \Aimeos\Upscheme\Up $up )
	{
		$this->up = $up;
	}


	/**
	 * Passes unknown method calls to the database scheme object
	 *
	 * @param string $method Method name
	 * @param array $args Method arguments
	 * @return mixed Result or database schema object
	 */
	public function __call( string $method, array $args )
	{
		return $this->db()->{$method}( ...$args );
	}


	/**
	 * Returns the list of task names which depends on this task
	 *
	 * @return string[] List of task names
	 */
	public function after() : array
	{
		return [];
	}


	/**
	 * Returns the list of task names which this task depends on
	 *
	 * @return string[] List of task names
	 */
	public function before() : array
	{
		return [];
	}


	/**
	 * Outputs the message depending on the verbosity
	 *
	 * @param string $msg Message to display
	 * @param int $level Level for indenting the message
	 * @return self Same object for fluid method calls
	 */
	protected function info( string $msg, int $level = 0 ) : self
	{
		$this->up->info( str_repeat( ' ', $level * 2 ) . $msg, 'v' );
		return $this;
	}


	/**
	 * Returns the database schema for the given connection name
	 *
	 * @param string|null $name Name of the connection from the configuration or NULL for first one
	 * @return \Aimeos\Upscheme\Schema\DB DB schema object
	 */
	protected function db( string $name = null ) : \Aimeos\Upscheme\Schema\DB
	{
		return $this->up->db( $name );
	}
}