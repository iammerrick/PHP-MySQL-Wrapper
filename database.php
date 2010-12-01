<?php
	/**
	* Active Record - Ports some basic Utility Functionality of Rails Active Records to PHP
	*/
	class ActiveRecord
	{
		/**
		 * Constructor sets basic connection variables.
		 *
		 * @author Merrick Christensen
		 * @param $host:string - host, $username:string - mysql username, $password:string - mysql password, $database:string - database name
		 */
		function ActiveRecord($host, $username, $password, $database)
		{
			$this->host = $host;
			$this->username = $username;
			$this->password = $password;
			$this->database = $database;
			$this->connection = null;
			$this->connection_database = null;
			
			$this->connect();
		}
		
		/**
		 * Set up the connection and basic utility functions.
		 *
		 * @author Merrick Christensen
		 */
		
		/**
		 * Connect to the database.
		 *
		 * @author Merrick Christensen
		 * @return $ret:boolean
		 */
		private function connect()
		{
			$this->connection = mysql_connect($this->host, $this->username, $this->password);
			if(!$this->connection)
			{
				$this->error(mysql_error());
			}
			$this->select_database();
		}
		
		/**
		 * Disconnect from MySQL
		 *
		 * @return void
		 * @author Merrick Christensen
		 */
		private function disconnect()
		{
			mysql_close($this->connection);
		}
		
		/**
		 * Select which database to use
		 *
		 * @return void
		 * @author Merrick Christensen
		 */
		private function select_database()
		{
			$this->connection_database = mysql_select_db($this->database, $this->connection);
			
			if(!$this->connection_database)
			{
				$this->error(mysql_error());
			}
		}
		
		/**
		 * Stop script, print error
		 *
		 * @author Merrick Christensen
		 * @param $error:string
		 */
		private function error($error)
		{
			die ($error);
		}
		
		/**
		 * Now that everything is connected and our utilities are set up. Lets create some utilities.
		 *
		 * @author Merrick Christensen
		 */
		
		/**
		 * Run a MySQL Query and return the results.
		 *
		 * @author Merrick Christensen
		 * @param $query:string - a MySQL query.
		 * @return $ret:array
		 */
		public function query($query)
		{
			$ret = mysql_query($query);
			if(!$ret)
			{
				$this->error(mysql_error());
			}
			return $ret;
		}
		
		/**
		 * Create an array from the query.
		 *
		 * @author Merrick Christensen
		 */
		public function build_array($query)
		{
			$ret = array();
			
			$query_result = $this->query($query);
			while($row = mysql_fetch_assoc($query_result))
			{
				array_push($ret, $row);
			}
			return $ret;
		}
		
		/**
		 * Find basically builds query gives you an array. You can write me at merrick.christensen@gmail.com and thank me ;)
		 * If you don't pass anything, it will take everything in the table. If you pass an array it will create a WHERE based on the key => value pairs.
		 *
		 * @author Merrick Christensen
		 * @param $table:string - table name, $filter:array - key value pair for WHERE generation, $and:boolean should it be looking for OR or AND
		 */
		public function find($table, $filter = null, $and = false)
		{
			if(is_array($filter))
			{
				$refine = $this->build_where($filter, $and);
				
				$query = "SELECT * FROM $table " . $refine;
				$ret = $this->build_array($query);
				return $ret;
			}
			else
			{
				$query = "SELECT * FROM $table";
				$ret = $this->build_array($query);
				return $ret;
			}
		}
		
		/**
		 * Creates an item and inserts it into the table. Based on Key Value stuff yet again...
		 *
		 * @author Merrick Christensen
		 * @param $table:string - table name; $insert:array - key value of inserts
		 */
		public function create($table, $insert)
		{
			$key_values = $this->build_key_value($insert);
			$keys = $key_values['keys'];
			$values = $key_values['values'];
			$query_string = 'INSERT INTO ' . $table . ' (' . $keys . ')' . ' VALUES (' . $values . ')';
			$ret = $this->query($query_string);
			return $ret;
		}
		
		/**
		 * Updates an item in the passed table. Based on Key Value stuff.
		 *
		 * @author Merrick Christensen
		 * @param $table:string - table name; $where:array - see build_where; $update:array - key value of inserts
		 */
		public function update($table, $where, $update)
		{
			$set = $this->build_update($update);
			$where = $this->build_where($where);
			$query_string = 'UPDATE ' . $table . ' ' . $set . ' ' . $where;
			$ret = $this->query($query_string);
			return $ret;
		}
		
		/**
		 * Destroys an item.
		 *
		 * @author Merrick Christensen
		 * @param $table:string - table name; $where:array - key value on what to delete
		 */
		public function destroy($table, $where)
		{
			$where = $this->build_where($where);
			$query_string = 'DELETE FROM ' . $table . ' ' . $where;
			$ret = $this->query($query_string);
			return $ret;
		}
		
		/**
		 * Compiles a where statement statement base on an array.
		 *
		 * @author Merrick Christensen
		 * @param $filter:array - key value of WHERE key = value; $and:boolean - OR or AND
		 */
		private function build_where($filter, $and=false)
		{
			$ret = '';
			if($and == false)
			{
				$and = ' OR ';
			}
			else
			{
				$and = ' AND ';
			}
			
			foreach($filter as $key=>$value)
			{
				$key_explode = explode(' ', $key);
				
				// If it has an operator in it the reflect that.
				if(count($key_explode) > 1)
				{
					if(empty($ret))
					{
						$ret .= 'WHERE ' . $key_explode[0] . ' ' . $key_explode[1] . ' \'' . $value . '\'';
					} 
					else
					{
						$ret .= $and . $key_explode[0] . ' ' . $key_explode[1] . ' \'' . $value . '\'';
					}
				}
				// If it doesn't just use =
				else
				{
					if(empty($ret))
					{
						$ret .= 'WHERE ' . $key . ' = \'' . $value . '\'';
					} 
					else
					{
						$ret .= $and . $key . ' = \'' . $value . '\'';
					}
				}
			}
			
			return $ret;
		}
		
		/**
		 * Build update string from array.
		 *
		 * @author Merrick Christensen
		 * @param $update:array - creates "key = value"
		 */
		private function build_update($update)
		{
			$ret = 'SET ';
			
			foreach($update as $key=>$value)
			{
				$ret .= $key . ' = \'' . $this->escape($value) . '\', ';
			}
			
			$ret = substr($ret, 0, -2);
			return $ret;
		}
		
		/**
		 * Takes a key=>value array that will create a string of keys and a string of values. 
		 *
		 * @author Merrick Christensen
		 * @param $key_value:array - keys and values
		 */
		private function build_key_value($key_value)
		{
			$keys = '';
			$values = '';

			foreach($key_value as $key => $value)
			{
				$keys .= ''. $key . ', ';
				$values .= '"'. $this->escape($value) . '", ';
			}

			$keys = substr($keys, 0, -2);
			$values = substr($values, 0, -2);
			
			$ret = array('keys'=>$keys, 'values'=>$values);
			return $ret;
		}
		
		/**
		 * Escape value for MySQL injection prevention
		 *
		 * @author Merrick Christensen
		 * @param $string:String to be escaped
		 * @return $ret:string - escaped string
		 */
		private function escape($string)
		{
			$ret = mysql_real_escape_string($string);
			return $ret;
		}
	}
	
?>