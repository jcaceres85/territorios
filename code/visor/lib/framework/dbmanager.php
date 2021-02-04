<?php

class DBManager
{

	static $db_conn=null;
	
	static function connect()
	{
		self::$db_conn = pg_connect("host=db port=5432 dbname=geonode user=geonode password=geonode");

		//pg_set_client_encoding(self::$db_conn, "latin1");
		
	}

	static function close()
	{
		return pg_close(self::$db_conn);
	}
	
	public static function check_connection()
	{
		self::connect();
	
		$r = self::$db_conn ? 1 : 0;
		
		self::close();
	
		return $r;
	}

	public static function execute_query($sql_query)
	{
		self::connect();
		
		$result = pg_query(self::$db_conn, $sql_query);
		
		$num_rows = pg_num_rows($result);
		
		$rows = array();
		
		for($i=0; $i<$num_rows; $i++)
		{
			$rows[]= pg_fetch_array($result, $i);
		}
		
		//$pg_free_result($result);
		
		self::close();
		
		return $rows;
	}
	
	
	public static function execute_nonquery($sql_nonquery)
	{
		self::connect();
		
		$result = pg_query(self::$db_conn, $sql_nonquery);
		
		//$affected_rows = pg_affected_rows($result);
		
		self::close();
		
		return $result;
	}
	
	public static function execute_scalar($sql_scalar)
	{
		self::connect();
		
		$result = pg_query(self::$db_conn, $sql_scalar);
		
		$num_rows = pg_num_rows($result);
		
		
		if($num_rows>0)
		{
			$r = pg_fetch_array($result, 0);
			$object = $r[0];
		}
		
		
		self::close();
		
		return $object;
	}


	public static function execute_nonquery_ts($sql_nonquery)
	{
		
		$result = pg_query(self::$db_conn, $sql_nonquery);
			
		return $result;
	}


	public static function execute_scalar_ts($sql_scalar)
	{
		
		$result = pg_query(self::$db_conn, $sql_scalar);
		
		$num_rows = pg_num_rows($result);
		
		
		if($num_rows>0)
		{
			$r = pg_fetch_array($result, 0);
			$object = $r[0];
		}
		else
		{
			$object = null;
		}
		
		
		return $object;
	}

	
	
}

?>
