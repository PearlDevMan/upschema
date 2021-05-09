<?php

/**
 * @license LGPLv3, https://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2021
 */


namespace Aimeos\Upscheme\Schema;


/**
 * Table schema class
 */
class Table
{
	use \Aimeos\Upscheme\Macro;


	private $db;
	private $table;


	/**
	 * Initializes the table object
	 *
	 * @param \Aimeos\Upscheme\Schema\DB $db DB schema object
	 * @param \Doctrine\DBAL\Schema\Table $table Doctrine table object
	 */
	public function __construct( \Aimeos\Upscheme\Schema\DB $db, \Doctrine\DBAL\Schema\Table $table )
	{
		$this->db = $db;
		$this->table = $table;
	}


	/**
	 * Calls custom methods or passes unknown method calls to the Doctrine table object
	 *
	 * @param string $method Name of the method
	 * @param array $args Method parameters
	 * @return mixed Return value of the called method
	 */
	public function __call( string $method, array $args )
	{
		if( $fcn = self::macro( $method ) ) {
			return $this->call( $method, $args );
		}

		return $this->table->{$method}( ...$args );
	}


	/**
	 * Returns the value for the given table option
	 *
	 * @param string $name Table option name
	 * @return mixed Table option value
	 */
	public function __get( string $name )
	{
		return $this->opt( $name );
	}


	/**
	 * Sets the new value for the given table option
	 *
	 * @param string $name Table option name
	 * @param mixed Table option value
	 */
	public function __set( string $name, $value )
	{
		$this->opt( $name, $value );
	}


	/**
	 * Creates a new column of type "bigint" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function bigint( string $name ) : Column
	{
		return $this->col( $name, 'bigint' )->default( 0 );
	}


	/**
	 * Creates a new column of type "binary" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @param int $length Length of the column in bytes
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function binary( string $name, int $length = null ) : Column
	{
		return $this->col( $name, 'binary' )->length( $length )->default( '' );
	}


	/**
	 * Creates a new column of type "blob" or returns the existing one
	 *
	 * The maximum length of a "blob" column is 2GB.
	 *
	 * @param string $name Name of the column
	 * @param int $length Length of the column in bytes
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function blob( string $name, int $length = null ) : Column
	{
		return $this->col( $name, 'blob' )->length( $length )->default( '' );
	}


	/**
	 * Creates a new column of type "boolean" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function bool( string $name ) : Column
	{
		return $this->col( $name, 'boolean' )->default( false );
	}


	/**
	 * Creates a new column or returns the existing one
	 *
	 * If the column doesn't exist yet, it will be created.
	 *
	 * @param string $name Name of the column
	 * @param string $type Type of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function col( string $name, string $type ) : Column
	{
		if( $this->table->hasColumn( $name ) ) {
			$col = $this->table->getColumn( $name )->setType( \Doctrine\DBAL\Types\Type::getType( $type ) );
		} else {
			$col = $this->table->addColumn( $name, $type );
		}

		return new Column( $this, $col );
	}


	/**
	 * Creates a new column of type "date" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function date( string $name ) : Column
	{
		return $this->col( $name, 'date' );
	}


	/**
	 * Creates a new column of type "datetime" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function datetime( string $name ) : Column
	{
		return $this->col( $name, 'datetime' );
	}


	/**
	 * Creates a new column of type "datetimetz" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function datetimetz( string $name ) : Column
	{
		return $this->col( $name, 'datetimetz' );
	}


	/**
	 * Creates a new column of type "decimal" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @param int $digits Total number of decimal digits including decimals
	 * @param int $decimals Number of digits after the decimal point
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function decimal( string $name, int $digits, int $decimals = 2 ) : Column
	{
		return $this->col( $name, 'decimal' )->precision( $digits )->scale( $decimals )->default( 0 );
	}


	/**
	 * Creates a new column of type "float" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function float( string $name ) : Column
	{
		return $this->col( $name, 'float' )->default( 0 );
	}


	/**
	 * Creates a new column of type "integer" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function int( string $name ) : Column
	{
		return $this->col( $name, 'integer' )->default( 0 );
	}


	/**
	 * Creates a new column of type "json" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function json( string $name ) : Column
	{
		return $this->col( $name, 'json' )->default( '{}' );
	}


	/**
	 * Creates a new column of type "smallint" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function smallint( string $name ) : Column
	{
		return $this->col( $name, 'smallint' )->default( 0 );
	}


	/**
	 * Creates a new column of type "string" or returns the existing one
	 *
	 * This type should be used for up to 255 characters. For more characters,
	 * use the "text" type.
	 *
	 * @param string $name Name of the column
	 * @param int $length Length of the column in characters
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function string( string $name, int $length = 255 ) : Column
	{
		return $this->col( $name, 'string' )->length( $length )->default( '' );
	}


	/**
	 * Creates a new column of type "text" or returns the existing one
	 *
	 * The maximum length of a "text" column is 2GB.
	 *
	 * @param string $name Name of the column
	 * @param int $length Length of the column in characters
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function text( string $name, int $length = 0xffff ) : Column
	{
		return $this->col( $name, 'text' )->length( $length )->default( '' );
	}


	/**
	 * Creates a new column of type "time" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function time( string $name ) : Column
	{
		return $this->col( $name, 'time' );
	}


	/**
	 * Creates a new column of type "guid" or returns the existing one
	 *
	 * @param string $name Name of the column
	 * @return \Aimeos\Upscheme\Schema\Column Column object
	 */
	public function uuid( string $name ) : Column
	{
		return $this->col( $name, 'guid' );
	}


	/**
	 * Drops the column given by its name if it exists
	 *
	 * @param array|string $name Name of the column or columns
	 * @return self Same object for fluid method calls
	 */
	public function dropColumn( $name ) : self
	{
		foreach( (array) $name as $entry )
		{
			if( $this->table->hasColumn( $entry ) ) {
				$this->table->dropColumn( $entry );
			}
		}

		return $this;
	}


	/**
	 * Drops the index given by its name if it exists
	 *
	 * @param array|string $name Name of the index or indexes
	 * @return self Same object for fluid method calls
	 */
	public function dropIndex( $name ) : self
	{
		foreach( (array) $name as $entry )
		{
			if( $this->table->hasIndex( $entry ) ) {
				$this->table->dropIndex( $entry );
			}
		}

		return $this;
	}


	/**
	 * Drops the foreign key constraint given by its name if it exists
	 *
	 * @param array|string $name Name of the foreign key constraint or constraints
	 * @return self Same object for fluid method calls
	 */
	public function dropForeign( $name ) : self
	{
		foreach( (array) $name as $entry )
		{
			if( $this->table->hasForeignKey( $entry ) ) {
				$this->table->removeForeignKey( $entry );
			}
		}

		return $this;
	}


	/**
	 * Drops the primary key if it exists
	 *
	 * @return self Same object for fluid method calls
	 */
	public function dropPrimary() : self
	{
		if( $this->table->hasPrimaryKey() ) {
			$this->table->dropPrimaryKey();
		}

		return $this;
	}


	/**
	 * Drops the unique index given by its name if it exists
	 *
	 * @param array|string $name Name of the unique index or indexes
	 * @return self Same object for fluid method calls
	 */
	public function dropUnique( $name ) : self
	{
		foreach( (array) $name as $entry )
		{
			if( $this->table->hasUniqueConstraint( $entry ) ) {
				$this->table->removeUniqueConstraint( $entry );
			}
		}

		return $this;
	}


	/**
	 * Checks if the column exists
	 *
	 * @param array|string $name Name of the column or columns
	 * @return TRUE if the columns exists, FALSE if not
	 */
	public function hasColumn( $name ) : bool
	{
		foreach( (array) $name as $entry )
		{
			if( !$this->table->hasColumn( $entry ) ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Checks if the index exists
	 *
	 * @param array|string $name Name of the index or indexes
	 * @return TRUE if the indexes exists, FALSE if not
	 */
	public function hasIndex( $name ) : bool
	{
		foreach( (array) $name as $entry )
		{
			if( !$this->table->hasIndex( $entry ) ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Checks if the foreign key constraint exists
	 *
	 * @param array|string $name Name of the foreign key constraint or constraints
	 * @return TRUE if the foreign key constraints exists, FALSE if not
	 */
	public function hasForeign( $name ) : bool
	{
		foreach( (array) $name as $entry )
		{
			if( !$this->table->hasForeignKey( $entry ) ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Checks if the unique index exists
	 *
	 * @param array|string $name Name of the unique index or indexes
	 * @return TRUE if the unique indexes exists, FALSE if not
	 */
	public function hasUnique( $name ) : bool
	{
		foreach( (array) $name as $entry )
		{
			if( !$this->table->hasUniqueConstraint( $entry ) ) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Creates a new foreign key or returns the existing one
	 *
	 * @param array|string $localcolumn Name of the local column or columns
	 * @param string $foreigntable Name of the referenced table
	 * @param array|string $localcolumn Name of the referenced column or columns
	 * @param string|null Name of the foreign key constraint and foreign key index or NULL for autogenerated name
	 * @return \Aimeos\Upscheme\Schema\Foreign Foreign key constraint object
	 */
	public function foreign( $localcolumn, string $foreigntable, $foreigncolumn = 'id', string $name = null ) : Foreign
	{
		$localcolumn = (array) $localcolumn;
		$foreigncolumn = (array) $foreigncolumn;

		if( !$this->db->hasTable( $foreigntable ) ) {
			\RuntimeException( sprintf( 'Table "%1$s" is missing', $foreigntable ) );
		}

		$table = $this->db->table( $foreigntable );

		foreach( $foreigncolumn as $idx => $col )
		{
			if( !$table->hasColumn( $col ) ) {
				\RuntimeException( sprintf( 'Column "%1$s" in table "%2$s" is missing', $col, $table->name() ) );
			}

			if( !isset( $localcolumn[$idx] ) ) {
				\LogicException( sprintf( 'No matching local column for foreign column "%1$s" in table "%2$s"', $col ) );
			}

			$localcol = $localcolumn[$idx];
			$dbalcolumn = $table->getColumn( $col );
			$options = $dbalcolumn->toArray();
			unset( $options['name'], $options['autoincrement'] );

			if( $this->table->hasColumn( $localcol ) ) {
				$this->table->changeColumn( $localcol, $options );
			} else {
				$this->table->addColumn( $localcol, $dbalcolumn->getType()->getName(), $options );
			}
		}

		$name = $name ?: $this->nameIndex( $this->name(), $localcolumn, 'fk' );
		return new Foreign( $this, $this->table, $localcolumn, $foreigntable, $foreigncolumn, $name );
	}


	/**
	 * Creates a new index or replaces an existing one
	 *
	 * @param array|string $columns Name of the columns or columns spawning the index
	 * @param string|null $name Index name or NULL for autogenerated name
	 * @return self Same object for fluid method calls
	 */
	public function index( $columns, string $name = null ) : self
	{
		$columns = (array) $columns;
		$name = $name ?: $this->nameIndex( $this->name(), $columns, 'idx' );

		if( $name && $this->table->hasIndex( $name ) )
		{
			if( $this->table->getIndex( $name )->spansColumns( $columns ) ) {
				return $this;
			}

			$this->table->dropIndex( $name );
		}

		$this->table->addIndex( $columns, $name );
		return $this;
	}


	/**
	 * Returns the name of the table
	 *
	 * @return string Table name
	 */
	public function name() : string
	{
		return $this->table->getName();
	}


	/**
	 * Sets a custom schema option or returns the current value
	 *
	 * Available custom schema options are:
	 * - charset (MySQL)
	 * - collation (MySQL)
	 * - engine (MySQL)
	 * - temporary (MySQL)
	 *
	 * @param string $name Name of the table-related custom schema option
	 * @param mixed $value Value of the custom schema option
	 * @return self|mixed Same object for setting value, current value without second parameter
	 */
	public function opt( string $name, $value = null )
	{
		if( $value === null ) {
			return $this->table->getOption( $option );
		}

		$this->table->addOption( $name, $value );
		return $this;
	}


	/**
	 * Creates a new primary index or replaces an existing one
	 *
	 * @param array|string $columns Name of the columns or columns spawning the index
	 * @param string|null $name Index name or NULL for autogenerated name
	 * @return self Same object for fluid method calls
	 */
	public function primary( $columns, string $name = null ) : self
	{
		$columns = (array) $columns;
		$index = $this->table->getPrimaryKey();
		$name = $name ?: $this->nameIndex( $this->name(),  $columns, 'pk' );

		if( $index && $index->spansColumns( $columns ) ) {
			return $this;
		} elseif( $index ) {
			$this->table->dropPrimaryKey();
		}

		$this->table->setPrimaryKey( $columns, $name );
		return $this;
	}


	/**
	 * Renames an index or a list of indexes
	 *
	 * @param array|string $from Index name or array of old/new index names (if new index name is NULL, it will be generated)
	 * @param string|null $to New index name or NULL for autogenerated name (ignored if first parameter is an array)
	 * @return self Same object for fluid method calls
	 */
	public function renameIndex( $from, string $to = null ) : self
	{
		if( !is_array( $from ) ) {
			$from = [$from => $to];
		}

		foreach( $from as $name => $to )
		{
			if( $this->table->hasIndex( $name ) )
			{
				if( !$to )
				{
					$columns = $this->table->getIndex( $name )->getColumns();
					$to = $this->nameIndex( $this->name(), $columns, 'idx' );
				}

				$this->table->renameIndex( $name, $to );
			}
		}

		return $this;
	}


	/**
	 * Creates a new spatial index or replaces an existing one
	 *
	 * @param array|string $columns Name of the columns or columns spawning the index
	 * @param string|null $name Index name or NULL for autogenerated name
	 * @return self Same object for fluid method calls
	 */
	public function spatial( $columns, string $name = null ) : self
	{
		$columns = (array) $columns;
		$name = $name ?: $this->nameIndex( $this->name(), $columns, 'idx' );

		if( $name && $this->table->hasIndex( $name ) )
		{
			$index = $this->table->getIndex( $name );

			if( $index->hasFlag( 'spatial' ) && $index->spansColumns( $columns ) ) {
				return $this;
			}

			$this->table->dropIndex( $name );
		}

		$this->table->addIndex( $columns, $name, ['spatial' => true] );
		return $this;
	}


	/**
	 * Creates a new unique index or replaces an existing one
	 *
	 * @param array|string $columns Name of the columns or columns spawning the index
	 * @param string|null $name Index name or NULL for autogenerated name
	 * @return self Same object for fluid method calls
	 */
	public function unique( $columns, string $name = null ) : self
	{
		$columns = (array) $columns;
		$name = $name ?: $this->nameIndex( $this->name(), $columns, 'unq' );

		if( $name && $this->table->hasIndex( $name ) )
		{
			$index = $this->table->getIndex( $name );

			if( $index->isUnique() && $index->spansColumns( $columns ) ) {
				return $this;
			}

			$this->table->dropIndex( $name );
		}

		$this->table->addUniqueIndex( $columns, $name );
		return $this;
	}


	/**
	 * Applies the changes to the database schema
	 *
	 * @return self Same object for fluid method calls
	 */
	public function up() : self
	{
		$this->db->up();
		return $this;
	}


	/**
	 * Returns the name that should be used for the index
	 *
	 * Available types are:
	 * - idx: Regular index
	 * - fk: Foreign key index
	 * - pk: Primary key index
	 * - unq: Unique index
	 *
	 * @param string $table Table name
	 * @param array $columns Column names
	 * @param string $type Index type
	 * @return string|null Name of the index or NULL to use the generated name by Doctrine DBAL
	 */
	protected function nameIndex( string $table, array $columns, string $type = 'idx' ) : ?string
	{
		if( $fcn = self::macro( 'nameIndex' ) ) {
			return $fcn( $table, $columns, $type );
		}

		return null;
	}
}