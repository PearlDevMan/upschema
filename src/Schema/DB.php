<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021
 */


namespace Aimeos\Upscheme\Schema;


/**
 * Database scheme manager class
 */
class DB
{
	private $conn;
	private $from;
	private $to;
	private $up;


	/**
	 * Initializes the database schema manager object
	 *
	 * @param \Aimeos\Upscheme\Up $up Main Upscheme object
	 * @param \Doctrine\DBAL\Connection $conn Doctrine database connection
	 */
	public function __construct( \Aimeos\Upscheme\Up $up, \Doctrine\DBAL\Connection $conn )
	{
		$this->from = $conn->getSchemaManager()->createSchema();
		$this->to = clone $this->from;

		$this->conn = $conn;
		$this->up = $up;
	}


	/**
	 * Passes unknown method calls to the Doctrine scheme object
	 *
	 * @param string $method Name of the method
	 * @param array $args Method parameters
	 * @return mixed Return value of the called method
	 */
	public function __call( string $method, array $args )
	{
		return $this->to->{$method}( ...$args );
	}


	/**
	 * Clones the internal objects
	 */
	public function __clone()
	{
		$this->up();

		$this->to = clone $this->to;
		$this->conn = clone $this->conn;

		$this->conn->close();
	}


	/**
	 * Closes the database connection
	 */
	public function close()
	{
		$this->conn->close();
	}


	/**
	 * Deletes the records from the given table
	 *
	 * Warning: The condition values are escaped but the table name and condition
	 * column names are not! Only use fixed strings for table name and condition
	 * column names but no external input!
	 *
	 * @param string $table Name of the table
	 * @param array|null $conditions Key/value pairs of column names and value to compare with
	 * @return self Same object for fluid method calls
	 */
	public function delete( string $table, array $conditions = null ) : self
	{
		$this->conn->delete( $table, $conditions ?? [1 => 1] );
		return $this;
	}


	/**
	 * Drops the column given by its name
	 *
	 * @param string $table Name of the table the column belongs to
	 * @param string $name Name of the column
	 * @return self Same object for fluid method calls
	 */
	public function dropColumn( string $table, string $name ) : self
	{
		if( $this->hasColumn( $name ) ) {
			$this->table( $table )->dropColumn( $name );
		}

		return $this;
	}


	/**
	 * Drops the foreign key constraint given by its name
	 *
	 * @param string $table Name of the table the foreign key constraint belongs to
	 * @param string $name Name of the foreign key constraint
	 * @return self Same object for fluid method calls
	 */
	public function dropForeign( string $table, string $name ) : self
	{
		if( $this->hasForeign( $table, $name ) ) {
			$this->table( $table )->dropForeign( $name );
		}

		return $this;
	}


	/**
	 * Drops the index given by its name
	 *
	 * @param string $table Name of the table the index belongs to
	 * @param string $name Name of the index
	 * @return self Same object for fluid method calls
	 */
	public function dropIndex( string $table, string $name ) : self
	{
		if( $this->hasIndex( $table, $name ) ) {
			$this->table( $table )->dropIndex( $name );
		}

		return $this;
	}


	/**
	 * Drops the sequence given by its name
	 *
	 * @param string $name Name of the sequence
	 * @return self Same object for fluid method calls
	 */
	public function dropSequence( string $name ) : self
	{
		if( $this->hasSequence( $name ) ) {
			$this->to->dropSequence( $name );
		}

		return $this;
	}


	/**
	 * Drops the table given by its name
	 *
	 * @param string $name Name of the table
	 * @return self Same object for fluid method calls
	 */
	public function dropTable( string $name ) : self
	{
		if( $this->hasTable( $name ) ) {
			$this->to->dropTable( $name );
		}

		return $this;
	}


	/**
	 * Drops the unique index given by its name
	 *
	 * @param string $table Name of the table the unique index belongs to
	 * @param string $name Name of the unique index
	 * @return self Same object for fluid method calls
	 */
	public function dropUnique( string $table, string $name ) : self
	{
		if( $this->hasUnique( $table, $name ) ) {
			$this->table( $table )->dropUnique( $name );
		}

		return $this;
	}


	/**
	 * Executes a custom SQL statement if the database is of the given type
	 *
	 * The database changes are not applied immediately so always call up()
	 * before executing custom statements to make sure that the tables you want
	 * to use has been created before!
	 *
	 * @param string $type Database type
	 * @param string $sql Custom SQL statement
	 * @return self Same object for fluid method calls
	 * @see type() method for available types
	 */
	public function for( string $type, string $sql ) : self
	{
		if( $this->type() === $type ) {
			$this->conn->executeStatement( $sql );
		}

		return $this;
	}


	/**
	 * Checks if the column exists
	 *
	 * @param string $table Name of the table the column belongs to
	 * @param string $name Name of the column
	 * @return TRUE if the column exists, FALSE if not
	 */
	public function hasColumn( string $table, string $name ) : bool
	{
		if( $this->hasTable( $table ) ) {
			return $this->table( $name )->hasColumn( $name );
		}

		return false;
	}


	/**
	 * Checks if the foreign key constraint exists
	 *
	 * @param string $table Name of the table the foreign key constraint belongs to
	 * @param string $name Name of the foreign key constraint
	 * @return TRUE if the foreign key constraint exists, FALSE if not
	 */
	public function hasForeign( string $table, string $name ) : bool
	{
		if( $this->hasTable( $table ) ) {
			return $this->table( $name )->hasForeign( $name );
		}

		return false;
	}


	/**
	 * Checks if the index exists
	 *
	 * @param string $table Name of the table the index belongs to
	 * @param string $name Name of the index
	 * @return TRUE if the index exists, FALSE if not
	 */
	public function hasIndex( string $table, string $name ) : bool
	{
		if( $this->hasTable( $table ) ) {
			return $this->table( $name )->hasIndex( $name );
		}

		return false;
	}


	/**
	 * Checks if the sequence exists
	 *
	 * @param string $name Name of the sequence
	 * @return TRUE if the sequence exists, FALSE if not
	 */
	public function hasSequence( string $name ) : bool
	{
		return $this->to->hasSequence( $name );
	}


	/**
	 * Checks if the table exists
	 *
	 * @param string $name Name of the table
	 * @return TRUE if the table exists, FALSE if not
	 */
	public function hasTable( string $name ) : bool
	{
		return $this->to->hasTable( $name );
	}


	/**
	 * Checks if the unique index exists
	 *
	 * @param string $table Name of the table the unique index belongs to
	 * @param string $name Name of the unique index
	 * @return TRUE if the unique index exists, FALSE if not
	 */
	public function hasUnique( string $table, string $name ) : bool
	{
		if( $this->hasTable( $table ) ) {
			return $this->to->getTable( $name )->hasUnique( $name );
		}

		return false;
	}


	/**
	 * Inserts a record into the given table
	 *
	 * Warning: The data values are escaped but the table name and column names are not!
	 * Only use fixed strings for table name and column names but no external input!
	 *
	 * @param string $table Name of the table
	 * @param array $data Key/value pairs of column name/value to insert
	 * @return self Same object for fluid method calls
	 */
	public function insert( string $table, array $data ) : self
	{
		$this->conn->insert( $table, $data );
		return $this;
	}


	/**
	 * Returns the records from the given table
	 *
	 * Warning: The condition values are escaped but the table name and condition
	 * column names are not! Only use fixed strings for table name and condition
	 * column names but no external input!
	 *
	 * If you need more control over what is returned, use the query builder
	 * from the stmt() method instead.
	 *
	 * @param string $table Name of the table
	 * @param array|null $conditions Key/value pairs of column names and value to compare with
	 * @return array List of associative arrays containing column name/value pairs
	 */
	public function select( string $table, array $conditions = null ) : array
	{
		$idx = 0;
		$result = [];

		$stmt = $this->conn->createQueryBuilder()->select()->from( $table );

		foreach( $conditions ?? [] as $column => $value ) {
			$stmt->where( $column . ' = ?' )->setParameter( $idx, $value );
		}

		$result = $stmt->executeQuery();

		while( $row = $stmt->fetchAssociative() ) {
			$result[] = $row;
		}

		return $result;
	}


	/**
	 * Returns the sequence object for the given name
	 *
	 * If the sequence doesn't exist yet, it will be created.
	 *
	 * @param string $name Name of the sequence
	 * @return \Aimeos\Upscheme\Schema\Sequence Sequence object
	 */
	public function sequence( string $name ) : Sequence
	{
		if( $this->hasSequence( $name ) ) {
			$seq = $this->to->getSequence( $name );
		} else {
			$seq = $this->to->createSequence( $name );
		}

		return new Sequence( $this->to, $seq );
	}


	/**
	 * Returns the query builder for a new SQL statement
	 *
	 * @return \Doctrine\DBAL\Query\QueryBuilder Query builder object
	 */
	public function stmt() : \Doctrine\DBAL\Query\QueryBuilder
	{
		return $this->conn->createQueryBuilder();
	}


	/**
	 * Returns the table object for the given name
	 *
	 * If the table doesn't exist yet, it will be created.
	 *
	 * @param string $name Name of the table
	 * @param \Closure|null $fcn Anonymous function with ($table) parameter creating or updating the table definition
	 * @return \Aimeos\Upscheme\Schema\Table Table object
	 */
	public function table( string $name, \Closure $fcn = null ) : Table
	{
		if( $this->hasTable( $name ) ) {
			$dt = $this->to->getTable( $name );
		} else {
			$dt = $this->to->createTable( $name );
		}

		$table = new Table( $this, $dt );

		if( $fcn )
		{
			$fcn( $table );
			$table->up();
		}

		return $table;
	}


	/**
	 * Returns the type of the database
	 *
	 * Possible values are:
	 * - db2
	 * - mysql
	 * - oracle
	 * - postgresql
	 * - mssql
	 * - sqlite
	 *
	 * @return string Database type
	 */
	public function type() : string
	{
		return $this->conn->getDatabasePlatform()->getName();
	}


	/**
	 * Applies the changes to the database schema
	 *
	 * @return self Same object for fluid method calls
	 */
	public function up() : self
	{
		foreach( $this->from->getMigrateToSql( $this->to, $this->conn->getDatabasePlatform() ) as $sql )
		{
			$this->up->info( '  ->  ' . $sql, 'vvv' );
			$this->conn->query( $sql );
		}

		unset( $this->from );
		$this->from = clone $this->to;

		return $this;
	}


	/**
	 * Updates the records from the given table
	 *
	 * Warning: The condition and data values are escaped but the table name and
	 * column names are not! Only use fixed strings for table name and condition
	 * column names but no external input!
	 *
	 * @param string $table Name of the table
	 * @param array $data Key/value pairs of column name/value to update
	 * @param array|null $conditions Key/value pairs of column names and value to compare with
	 * @return self Same object for fluid method calls
	 */
	public function update( string $table, array $data, array $conditions = null ) : self
	{
		$this->conn->update( $table, $data, $conditions ?? [1 => 1] );
		return $this;
	}
}