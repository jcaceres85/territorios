<?php

require_once("dbmanager.php");

class OAuth
{

	
	public static function get_accesstoken($token)
	{
		$sql_query = "SELECT * FROM oauth2_provider_accesstoken WHERE token='$token'";

		$objects = DBManager::execute_query($sql_query);

		return $objects;
	}

    public static function is_session_expired($token)
    {
        $sql_query = "SELECT NOW()>expires FROM oauth2_provider_accesstoken WHERE token='$token'";

        $objects = DBManager::execute_scalar($sql_query);

        return $objects;
    }


    public static function get_user_data_by_accesstoken($token)
    {
        $sql_query = "SELECT p.id,p.is_superuser,p.username FROM oauth2_provider_accesstoken a INNER JOIN people_profile p ON a.user_id=p.id WHERE token='$token'";

        $objects = DBManager::execute_query($sql_query);

        return $objects;
    }



}

?>


