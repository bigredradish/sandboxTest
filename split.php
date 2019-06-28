<?php
	$cookie_name = "eww_split";

	if(!isset($_COOKIE[$cookie_name])) {
		//get the IP
		//$ip=$_SERVER['REMOTE_ADDR'];
		$ip = '2001:41d0:d:1565:a:0:0:4';
		//hash it
		$hash = sha1($ip);
		//hex to decimal decode the first 4 of the hash
		$number = hexdec(substr($hash, 0, 4));
		//set the e.g. the number to the cookie (e.g. 90 day cookie)
	    setcookie($cookie_name, $number, time() + (86400 * 90), "/"); //86400 = 1 day
	} else {
	    $number = $_COOKIE[$cookie_name];
	}
	//work out modulus 2 of $number to give 0 or 1
	$version = $number % 2;

	//if no remainder give A else B
	if( $version == 0) {
		echo 'You got A';
		//e.g. load part of a view
	} else {
		echo 'You got B';
		//e.g. load part of a view
	}