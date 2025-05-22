<?php
    $dsn = 'mysql:host=localhost;dbname=restaurant_website';
	$user = 'root';
	$pass = '';
	
	// Check if PDO::MYSQL_ATTR_INIT_COMMAND constant is defined
	if(defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
	    $option = array(
		    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
	    );
	} else {
	    $option = array();
	}
	
	try
	{
		$con = new PDO($dsn,$user,$pass,$option);
		$con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		//echo 'Good Very Good !';
	}
	catch(PDOException $ex)
	{
		echo "Failed to connect with database ! ".$ex->getMessage();
		die();
	}
?>