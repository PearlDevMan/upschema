<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021
 */


namespace Aimeos\Upscheme;


/**
 * Main class for updating database schemas
 */
class Up
{
	private static $paths = [];
	private static $fcn = [];

	private $verbose = 0;
	private $config;
	private $db = [];
	private $tasks;
	private $tasksDone;
	private $dependencies;


	/**
	 * Initializes the new object
	 *
	 * @param array $config One or more database configuration parameters
	 * @param array|string One or more paths to the tasks which updates the database
	 */
	public function __construct( array $config, $paths = [] )
	{
		self::$paths = (array) $paths;

		if( spl_autoload_register( 'Aimeos\Upscheme\Up::autoload' ) === false ) {
			throw new \RuntimeException( 'Unable to register Aimeos\Upscheme\Up::autoload' );
		}

		if( empty( $config ) ) {
			throw new \RuntimeException( 'No database configuration passed' );
		}

		$this->config = $config;
	}


	/**
	 * Autoloader for setup tasks.
	 *
	 * @param string $classname Name of the class to load
	 * @return bool True if class was found, false if not
	 */
	public static function autoload( string $classname ) : bool
	{
		if( !strncmp( $classname, 'Aimeos\Upscheme\Task\\', 21 ) )
		{
			$fileName = substr( $classname, 21 ) . '.php';

			foreach( self::$paths as $path )
			{
				$file = $path . '/' . $fileName;

				if( file_exists( $file ) === true && ( include_once $file ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * Registers a custom method to extend an existing one
	 *
	 * @param string $method Name of the method to extend
	 * @param \Closure $fcn Anonymous function which receives the same parameters as the original method
	 */
	public static function method( string $method, \Closure $fcn )
	{
		self::$fcn[$name] = $fcn;
	}


	/**
	 * Creates a new Upscheme object initialized with the given configuration and paths
	 *
	 * @param array $config One or more database configuration parameters
	 * @param array|string One or more paths to the tasks which updates the database
	 * @return \Aimeos\Upscheme\Up Upscheme object
	 */
	public static function use( array $config, $paths = [] )
	{
		return new self( $config, $paths );
	}


	/**
	 * Returns the DB schema for the passed connection name
	 *
	 * @param string|null $name Name of the connection from the configuration or NULL for first one
	 * @param bool $new If a new connection should be created instead of reusing an existing one
	 * @return \Aimeos\Upscheme\Schema\DB DB schema object
	 */
	public function db( string $name = null, bool $new = false ) : \Aimeos\Upscheme\Schema\DB
	{
		if( !isset( $this->db[$name] ) )
		{
			$cfg = $this->config[$name] ?? ( is_array( $first = reset( $this->config ) ) ? $first : $this->config );

			$conn = isset( self::$fcn['conn'] ) ? self::$fcn['conn']( $cfg ) : $this->createConnection( $cfg );

			$this->db[$name] = new \Aimeos\Upscheme\Schema\DB( $this, $conn );
		}

		return $new ? clone $this->db[$name] : $this->db[$name];
	}


	/**
	 * Outputs the message depending on the passed verbosity level
	 *
	 * @param string $msg Message to display
	 * @param mixed $level Verbosity level (empty: always, v: notice: vv: info, vvv: debug)
	 * @return self Same object for fluid method calls
	 */
	public function info( string $msg, $level = 'v' ) : self
	{
		if( isset( self::$fcn['info'] ) )
		{
			self::$fcn['info']( $msg, $level );
			return $this;
		}

		if( $len = strlen( (string) $level ) <= $this->verbose ) {
			echo $msg . PHP_EOL;
		}

		return $this;
	}


	/**
	 * Executes the tasks to update the database
	 *
	 * @return self Same object for fluid method calls
	 */
	public function up() : self
	{
		$this->tasksDone = [];
		$this->dependencies = [];
		$this->tasks = $this->createTasks( self::$paths );

		foreach( $this->tasks as $name => $task )
		{
			foreach( (array) $task->after() as $taskname ) {
				$this->dependencies[$name][] = $taskname;
			}

			foreach( (array) $task->before() as $taskname ) {
				$this->dependencies[$taskname][] = $name;
			}
		}

		foreach( $this->tasks as $taskname => $task ) {
			$this->runTasks( [$taskname] );
		}

		return $this;
	}


	/**
	 * Sets the verbosity level
	 *
	 * @param mixed $level Verbosity level (empty: all, v: notice: vv: info, vvv: debug)
	 * @return self Same object for fluid method calls
	 */
	public function verbose( $level ) : self
	{
		if( isset( self::$fcn['verbose'] ) ) {
			$this->verbose = self::$fcn['verbose']( $level );
		} else {
			$this->verbose = strlen( (string) $level );
		}

		return $this;
	}


	/**
	 * Creates a new database connection from the given configuration
	 *
	 * @param array $cfg Database configuration
	 * @return \Doctrine\DBAL\Connection New DBAL database connection
	 */
	protected function createConnection( array $cfg ) : \Doctrine\DBAL\Connection
	{
		$cfg['driverOptions'][\PDO::ATTR_CASE] = \PDO::CASE_NATURAL;
		$cfg['driverOptions'][\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
		$cfg['driverOptions'][\PDO::ATTR_ORACLE_NULLS] = \PDO::NULL_NATURAL;
		$cfg['driverOptions'][\PDO::ATTR_STRINGIFY_FETCHES] = false;

		return \Doctrine\DBAL\DriverManager::getConnection( $cfg );
	}


	/**
	 * Creates the tasks from the given directories
	 *
	 * @param string[] $paths List of paths containing task classes
	 * @return \Aimeos\Upscheme\Task\Iface[] List of task objects
	 */
	protected function createTasks( array $paths ) : array
	{
		$tasks = [];

		foreach( $paths as $path )
		{
			foreach( new \DirectoryIterator( $path ) as $item )
			{
				if( $item->isDir() === true || substr( $item->getFilename(), -4 ) != '.php' ) { continue; }

				$this->includeFile( $item->getPathName() );

				$taskname = substr( $item->getFilename(), 0, -4 );
				$classname = '\Aimeos\Upscheme\Task\\' . $taskname;

				if( class_exists( $classname ) === false ) {
					throw new \RuntimeException( sprintf( 'Class "%1$s" not found', $classname ) );
				}

				$interface = \Aimeos\Upscheme\Task\Iface::class;
				$task = new $classname( $this );

				if( ( $task instanceof $interface ) === false ) {
					throw new \RuntimeException( sprintf( 'Class "%1$s" doesn\'t implement "%2$s"', $classname, '\Aimeos\Upscheme\Task\Iface' ) );
				}

				$task->_filename = $item->getPathName();
				$tasks[$taskname] = $task;
			}
		}

		return $tasks;
	}


	/**
	 * Includes a PHP file.
	 *
	 * @param string $pathname Path to the file including the file name
	 */
	protected function includeFile( string $pathname )
	{
		if( ( include_once $pathname ) === false ) {
			throw new \RuntimeException( sprintf( 'Unable to include file "%1$s"', $pathname ) );
		}
	}


	/**
	 * Executes each task depending of the task dependencies
	 *
	 * @param string[] $tasknames List of task names
	 * @param string[] $stack List of task names that are sheduled after this task
	 */
	protected function runTasks( array $tasknames, array $stack = [] )
	{
		foreach( $tasknames as $taskname )
		{
			if( in_array( $taskname, $this->tasksDone ) ) {
				continue;
			}

			if( in_array( $taskname, $stack ) )
			{
				$msg = 'Circular dependency for "%1$s" detected. Task stack: %2$s';
				throw new \RuntimeException( sprintf( $msg, $taskname, join( ', ', $stack ) ) );
			}

			$stack[] = $taskname;

			if( isset( $this->dependencies[$taskname] ) ) {
				$this->runTasks( (array) $this->dependencies[$taskname], $stack );
			}

			if( isset( $this->tasks[$taskname] ) )
			{
				$this->info( PHP_EOL . $this->tasks[$taskname]->_filename, 'vv' );
				$this->tasks[$taskname]->up();
			}

			$this->tasksDone[] = $taskname;
		}
	}
}