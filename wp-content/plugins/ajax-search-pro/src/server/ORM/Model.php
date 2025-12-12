<?php

namespace WPDRMS\ASP\ORM;

use wpdb;

if ( !defined('ABSPATH') ) {
	exit; // Exit if accessed directly
}

/**
 * Simple one file ORM for WordPress
 *
 * This is not a replacement for Doctrine or any other more complex system.
 * It is merely a simple implementation for specialized uses in Ajax Search Pro and Ajax Search Lite.
 *
 * Key takeaways:
 * - dbDelta handles when new columns are added and updated automatically, it does not have to be implemented
 * in each model. Just modify the static $columns variable as needed, the rest is handled automatically.
 * - dbDelta does NOT handle column removal
 * - Foreign Keys are NOT SUPPORTED due to the lack of possibility to check if a foreign key exists.
 *
 * @phpstan-type Conditions Array<string, string|int|array{
 *   operator?: string,
 *   value?: mixed
 *  }>
 */
abstract class Model {
	protected static string $table_name = '';

	/**
	 * @var Array<string, string>
	 */
	protected static array $columns = array();

	/**
	 * @var string|string[]
	 */
	protected static $primary_key = 'id';

	/**
	 * Creates the descendant Model tables
	 *
	 * Should be called on activation only.
	 *
	 * @see register_activation_hook()
	 */
	public static function createTable(): void {
		if ( static::$table_name === '' ) {
			return;
		}

		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table           = $wpdb->prefix . static::$table_name;
		$columns_sql     = array();
		$keys_sql        = array();
		$foreign_keys    = array();

		$is_foreign_key = function ( $key ) {
			return str_starts_with(strtoupper($key), 'FOREIGN KEY');
		};

		$is_index = function ( $key ) {
			foreach ( array( 'PRIMARY KEY', 'INDEX', 'KEY', 'UNIQUE KEY', 'FULLTEXT KEY' ) as $column_key ) {
				$key = strtoupper($key);
				if ( str_starts_with($key, $column_key) ) {
					return true;
				}
			}
			return false;
		};

		foreach ( static::$columns as $key => $column ) {
			if ( $is_foreign_key($key) ) {
				$foreign_keys [] = $key;
				continue;
			}
			if ( $is_index($key) ) {
				$keys_sql [] = "$key $column";
			} else {
				$columns_sql [] = "$key $column";
			}
		}

		/**
		 * The dbDelta function separates fields via the \n newline character
		 * Mind the double quotes, otherwise \n is literal
		 */
		$full_sql = implode(", \n", array_merge($columns_sql, $keys_sql));

		$sql = "CREATE TABLE $table ( $full_sql ) $charset_collate;";

		/**
		 * In unit tests it is not possible to mock dbDelta
		 * as the Model class is used on activation hook before the test starts
		 * and is already bound to the original dbDelta.
		 *
		 * @see https://github.com/php-mock/php-mock-phpunit?tab=readme-ov-file
		 * "The mock has to be defined before the first call to the unqualified
		 * function in the tested class. This is documented in Bug #68541.
		 * In most cases you can ignore this restriction.
		 * But if you happen to run into this issue you can call PHPMock::defineFunctionMock()
		 * before that first call (e.g. with @beforeClass).
		 * This would define a side effectless namespaced function.
		 * Another effective approach is running your test in an isolated
		 * process (e.g. with @runInSeparateProcess)."
		 */
		if ( asp_get_global_function_mock( 'dbDelta') ) {
			asp_run_global_function_mock( 'dbDelta', $sql);
		} else {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta($sql);
		}
	}

	/**
	 * Deletes the model table from the database
	 *
	 * @return void
	 */
	public static function dropTable(): void {
		if ( static::$table_name === '' ) {
			return;
		}

		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;
		$table = $wpdb->prefix . static::$table_name;
		$q     = "DROP TABLE IF EXISTS `$table`;";
		$wpdb->query($q); // @phpcs:ignore
	}

	/**
	 * Truncates the model table from the database
	 *
	 * @return void
	 */
	public static function truncateTable(): void {
		if ( static::$table_name === '' ) {
			return;
		}

		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;
		$table = $wpdb->prefix . static::$table_name;
		$q     = "TRUNCATE TABLE `$table`;";
		$wpdb->query($q); // @phpcs:ignore
	}

	/**
	 * Get the Model table name
	 *
	 * @return string
	 */
	public static function getTableName(): string {
		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;
		return $wpdb->prefix . static::$table_name;
	}

	/**
	 * Create a new descendant Model instance and persistently save it in the database
	 *
	 * @param Array<string, int|string> $attributes
	 * @return static
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function create( array $attributes ) {
		$model = new static();
		foreach ( array_intersect_key($attributes, static::$columns) as $key => $value ) {
			$model->$key = $value;
		}
		$model->save();
		return $model;
	}

	/**
	 * Find a record by primary key
	 *
	 * Works for both single and multiple columned primary keys
	 *
	 * @param int|int[]|string[] $id
	 * @return static|null
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public static function find( $id ) {
		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		$where_arr = array();
		$value_arr = array();
		if ( is_array(static::$primary_key) ) {
			if ( !is_array($id) || count($id) !== count(static::$primary_key) ) {
				return null;
			}
			foreach ( $id as $k => $value ) {
				if ( !isset(static::$primary_key[ $k ]) ) {
					return null;
				}
				$where_arr [] = static::$primary_key[ $k ] . ( is_numeric(static::$primary_key[ $k ]) ? ' = %d' : ' = %s' );
				$value_arr [] = $value;
			}
		} else {
			if ( is_array($id) ) {
				return null;
			}
			$where_arr [] = static::$primary_key . ( is_numeric(static::$primary_key) ? ' = %d' : ' = %s' );
			$value_arr [] = $id;
		}

		if ( empty($where_arr) ) {
			return null;
		}

		$where_sql = implode(' AND ', $where_arr);

		/**
		 * @var null|Array<string, mixed> $result
		 */
		$result = $wpdb->get_row( // phpcs:ignore
			$wpdb->prepare(
				"SELECT * FROM ".static::getTableName()." WHERE $where_sql", // phpcs:ignore
				$value_arr,
			),
			ARRAY_A
		);
		if ( $result === null ) {
			return null;
		}
		$model = new static();
		foreach ( $result as $key => $value ) {
			$model->$key = $value;
		}
		return $model;
	}

	/**
	 * Find records by WHERE conditions
	 *
	 * @param Conditions $conditions  e.g., ['phrase' => 'test'] (=), ['date' => ['operator' => '>', 'value' => '2024-01-01']], ['id' => ['operator' => 'IN', 'value' => [1,2,3]]]
	 * @param int        $limit
	 * @param int        $offset
	 * @param string     $order_by
	 * @param string     $order
	 * @return static[]
	 */
	public static function findBy( array $conditions = array(), int $limit = 10, int $offset = 0, string $order_by = 'id', string $order = 'DESC' ): array {
		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		$where_clauses = array( '1=1' );
		$params        = array();
		foreach ( $conditions as $column => $cond ) {
			if ( !is_array($cond) ) {
				// Backward compat: simple value = '='
				$where_clauses[] = "$column = %s";
				$params[]        = $cond;
			} else {
				$operator = strtoupper($cond['operator'] ?? '=');
				$value    = $cond['value'] ?? null;

				switch ( $operator ) {
					case '=':
						$where_clauses[] = "$column = %s";
						$params[]        = $value;
						break;
					case '<>':
					case '!=':
						$where_clauses[] = "$column <> %s";
						$params[]        = $value;
						break;
					case '>':
						$where_clauses[] = "$column > %s";
						$params[]        = $value;
						break;
					case '<':
						$where_clauses[] = "$column < %s";
						$params[]        = $value;
						break;
					case '>=':
						$where_clauses[] = "$column >= %s";
						$params[]        = $value;
						break;
					case '<=':
						$where_clauses[] = "$column <= %s";
						$params[]        = $value;
						break;
					case 'LIKE':
						$where_clauses[] = "$column LIKE %s";
						$params[]        = $value;
						break;
					case 'NOT LIKE':
						$where_clauses[] = "$column NOT LIKE %s";
						$params[]        = $value;
						break;
					case 'IN':
						if ( is_array($value) ) {
							$placeholders    = implode(',', array_fill(0, count($value), '%s'));
							$where_clauses[] = "$column IN ($placeholders)";
							$params          = array_merge($params, $value);
						}
						break;
					case 'NOT IN':
						if ( is_array($value) ) {
							$placeholders    = implode(',', array_fill(0, count($value), '%s'));
							$where_clauses[] = "$column NOT IN ($placeholders)";
							$params          = array_merge($params, $value);
						}
						break;
					case 'BETWEEN':
						if ( is_array($value) && count($value) === 2 ) {
							$where_clauses[] = "$column BETWEEN %s AND %s";
							$params          = array_merge($params, $value);
						}
						break;
					case 'NOT BETWEEN':
						if ( is_array($value) && count($value) === 2 ) {
							$where_clauses[] = "$column NOT BETWEEN %s AND %s";
							$params          = array_merge($params, $value);
						}
						break;
					default:
						// Fallback to =
						$where_clauses[] = "$column = %s";
						$params[]        = $value;
				}
			}
		}

		$where_clause = implode(' AND ', $where_clauses);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'SELECT * FROM ' . static::getTableName() . " WHERE $where_clause ORDER BY $order_by $order LIMIT %d OFFSET %d",
				array_merge($params, array( $limit, $offset ))
			),
			ARRAY_A
		);

		if ( !is_array($results) ) {
			return array();
		}

		return array_map(
			function ( $data ) {
				$model = new static();
				foreach ( $data as $key => $value ) {
					$model->$key = $value;
				}
				return $model;
			},
			$results
		);
	}

	/**
	 * Inserts bulk records via a single query
	 *
	 * The passed objects should have the same number of attributes defined, otherwise it will fail.
	 *
	 * @param static[] $objects
	 * @return bool
	 */
	public static function bulkInsert( array $objects ): bool {
		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		if ( empty($objects) ) {
			return false;
		}
		$values  = array();
		$objects = array_filter(
			$objects,
			function ( $o ) {
				return ( $o instanceof static );
			}
		);
		if ( empty($objects) ) {
			return false;
		}
		reset($objects);
		$insert_attributes                = array();
		$insert_attributes_expected_count = null;
		foreach ( $objects as $object ) {
			$insert_attributes = array_intersect_key(get_object_vars($object), static::$columns);

			if ( is_array(static::$primary_key) ) {
				foreach ( static::$primary_key as $pk_column ) {
					if ( $insert_attributes[ $pk_column ] === 0 || $insert_attributes[ $pk_column ] === null ) {
						unset($insert_attributes[ $pk_column ]);
					}
				}
			} elseif ( isset($insert_attributes[ static::$primary_key ]) ) {
				if ( $insert_attributes[ static::$primary_key ] === 0 ) {
					unset($insert_attributes[ static::$primary_key ]);
				}
			}

			if ( $insert_attributes_expected_count === null ) {
				$insert_attributes_expected_count = count($insert_attributes);
			}

			if ( count($insert_attributes) !== $insert_attributes_expected_count ) {
				return false;
			}

			$row_values = array();

			foreach ( $insert_attributes as $column => $value ) {
				$row_values[] = $wpdb->prepare(self::getColumnFormat($column), $value); // @phpcs:ignore
			}
			$values[] = '(' . implode(',', $row_values) . ')';
		}
		if ( empty($insert_attributes) ) {
			return false;
		}

		$query = 'INSERT INTO ' . static::getTableName() . ' (' . implode(',', array_keys($insert_attributes)) . ') VALUES ' . implode(',', $values);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
		if ( $wpdb->query($query) !== false ) {
			foreach ( $objects as &$o ) {
				$o = null;
				unset($o);
			}
			return true;
		}
		return false;
	}

	/**
	 * Get the format for $wpdb->prepare query argument, either "%s" or "%d" based on the given column format
	 *
	 * @param string $column Column name
	 * @return string
	 */
	protected static function getColumnFormat( string $column ): string {
		if ( !isset(static::$columns[ $column ] ) ) {
			return '%s';
		}
		// Regex to detect numeric types (case-insensitive)
		if ( preg_match('/^(BIGINT|INT|SMALLINT|TINYINT|DECIMAL|FLOAT|DOUBLE|BOOL(?:EAN)?)\b/i', static::$columns[ $column ] ) ) {
			return '%d';
		}
		return '%s'; // Default for strings, dates, etc.
	}

	/**
	 * Get the format for $wpdb->prepare query argument, either "%s" or "%d" based on the given column format
	 *
	 * @param string[] $columns Array of column names
	 * @return string[]
	 */
	protected static function getFormat( array $columns ): array {
		$format = array();
		foreach ( $columns as $column ) {
			$format[] = self::getColumnFormat($column);
		}
		return $format;
	}

	final public function __construct() {}

	/**
	 * Updates or inserts a new record to the database
	 *
	 * Supports both single and composite primary keys. Returns the model on success, null on failure.
	 *
	 * @return static|null
	 */
	public function save(): ?self {
		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		$attributes = array_intersect_key(get_object_vars($this), static::$columns);

		if ( is_array(static::$primary_key) ) {
			// Composite primary key: Check if all PK columns have non-zero values
			$pk_values = array();
			foreach ( static::$primary_key as $pk_column ) {
				if ( isset($attributes[ $pk_column ]) && $attributes[ $pk_column ] !== 0 && $attributes[ $pk_column ] !== '' ) {
					$pk_values[ $pk_column ] = $attributes[ $pk_column ];
				} else {
					$pk_values[ $pk_column ] = null; // Missing/invalid
				}
			}
			$is_update =
				count($pk_values) === count(static::$primary_key) &&
				!in_array(null, $pk_values, true) &&
				static::find($pk_values) !== null;
		} else {
			// Single primary key: Check if set and non-zero
			$is_update = isset($attributes[ static::$primary_key ]) && $attributes[ static::$primary_key ] !== 0 && $attributes[ static::$primary_key ] !== '';
		}

		if ( $is_update ) {
			// Update
			if ( is_array(static::$primary_key) ) {
				// Composite PK
				$where        = array();
				$where_format = array();
				foreach ( static::$primary_key as $pk_column ) {
					$where[ $pk_column ] = $this->$pk_column; // Use model properties
					$where_format[]      = static::getFormat(array( $pk_column ))[0];
				}
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->update(
					static::getTableName(),
					$attributes,
					$where,
					static::getFormat(array_keys($attributes)),
					$where_format
				);
			} else {
				// Single PK
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->update(
					static::getTableName(),
					$attributes,
					array( static::$primary_key => $this->{static::$primary_key} ),
					static::getFormat(array_keys($attributes)),
					array( static::getFormat(array( static::$primary_key ))[0] )
				);
			}
			if ( $result === false ) {
				return null;
			}
			return $this; // Return updated model
		} else {
			// Insert: Exclude primary keys from attributes
			$insert_attributes = $attributes;
			if ( is_array(static::$primary_key) ) {
				foreach ( static::$primary_key as $pk_column ) {
					if ( $insert_attributes[ $pk_column ] === 0 || $insert_attributes[ $pk_column ] === null ) {
						unset($insert_attributes[ $pk_column ]);
					}
				}
			} elseif ( isset($insert_attributes[ static::$primary_key ]) ) {
				if ( $insert_attributes[ static::$primary_key ] === 0 ) {
					unset($insert_attributes[ static::$primary_key ]);
				}
			}
			foreach ( $insert_attributes as $col => $val ) {
				if (
					( $val === '' || $val === null ) &&
					str_contains(static::$columns[ $col ], 'DEFAULT CURRENT_TIMESTAMP')
				) {
					unset($insert_attributes[ $col ]);
				}
			}
			$result = $wpdb->insert(
				static::getTableName(),
				$insert_attributes,
				static::getFormat(array_keys($insert_attributes))
			);

			if ( $result === false ) {
				return null;
			}
			// Set auto-increment ID if single 'id' PK
			if ( static::$primary_key === 'id' ) {
				$this->id = $wpdb->insert_id;
			}
			// For composite PK inserts, assume user set them already; no auto-ID
			return $this; // Return inserted model with updated ID if applicable
		}
	}

	/**
	 * Delete instance
	 *
	 * @return bool
	 */
	public function delete(): bool {
		/**
		 * @var wpdb $wpdb
		 */
		global $wpdb;

		if ( !isset($this->{static::$primary_key}) || $this->{static::$primary_key} === 0 ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete(
			static::getTableName(),
			array( static::$primary_key => $this->{static::$primary_key} ),
			array( static::getFormat(array( static::$primary_key ))[0] )
		) !== false;
	}
}
