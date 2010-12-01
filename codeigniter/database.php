<?php
	/**
	* Active Record - Ports some basic Utility Functionality of Rails Active Records to PHP
	*/
	class ActiveRecord extends Model 
	{
		/**
		 * Constructor sets basic connection variables.
		 *
		 * @author Merrick Christensen
		 * @param $host:string - host, $username:string - mysql username, $password:string - mysql password, $database:string - database name
		 */
		function ActiveRecord($table)
		{
			parent::Model();
			$this->record = $table;
		}
		
		/**
		 * If you don't pass anything, it will take everything in the table. If you pass an array it will create a query based on options.
		 *
		 * @author Merrick Christensen
		 * @param $options:multi-dimenstional array 
		 * @available_keys
		 * order_by => array('column' => 'columnname', 'direction' => 'direction')
		 * where => array('key' => 'value', 'key2' => 'value2')
		 * limit => int
		 * join => array('table' => 'tablename', 'where' => 'field.id = other.field_id', 'left') 
		 */
		public function find($options = null)
		{
			
			$defaults = array(
				'order_by' => null,
				'where' => null,
				'limit' => null,
				'join' => null
			);
			
			$options = is_array($options) ? array_merge($defaults, $options) : $defaults;
			
			if($options['where'])
			{
				$this->db->where($options['where']);
			}
			
			if($options['order_by'])
			{
				$this->db->order_by($options['order_by']['column'], $options['order_by']['direction']);
			}
			
			if($options['limit'])
			{
				$this->db->limit($options['limit']);
			}
			
			if($options['join'])
			{
				if($options['join']['method'])
				{
					$this->db->join($options['join']['table'], $options['join']['where'], $options['join']['method']);
				}
				else
				{
					$this->db->join($options['join']['table'], $options['join']['where']);
				}
			}
					
			$ret = $this->db->get($this->record);
			
			return $ret;
		}
		
		/**
		 * Creates an item and inserts it into the table. Based on Key Value stuff yet again...
		 *
		 * @author Merrick Christensen
		 * @param $insert:array - key value of inserts
		 */
		public function create($insert)
		{
			$ret = $this->db->insert($this->record, $insert);
			return $ret;
		}
		
		/**
		 * Updates an item in the passed table. Based on Key Value stuff.
		 *
		 * @author Merrick Christensen
		 * @param $where:array - see build_where; $update:array - key value of inserts
		 */
		public function update($where, $update)
		{
			$this->db->where($where);
			$ret = $this->db->update($this->record, $update);
			return $ret;
		}
		
		/**
		 * Destroys an item.
		 *
		 * @author Merrick Christensen
		 * @param $where:array - key value on what to delete
		 */
		public function destroy($where)
		{
			$ret = $this->db->delete($this->record, $where);
			return $ret;
		}
	}
	
?>