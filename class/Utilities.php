<?php 

if ( !defined ( "FROM_INDEX" ) ) 
	die ( "Stop trying to hack the system!" );

if ( class_exists ( "Utilities" ) )
	die ( );
	
interface iUtilities {
	
	/**
	 * randomString 
	 * Returns a random string consisting of numbers and letters 
	 *
	 * @param	int		$length=10		The length that the returned string will be
	 * @return string
	**/
	public static function randomString ( $length );
	
	
}
	
class Utilities implements iUtilities { 

	public static function randomString ( $length=10 ) {
		
		$keys = array_merge(range(0,9), range('a', 'z'));
		
		$key = "";
		for($i=0; $i < $length; $i++)
			$key .= $keys[array_rand($keys)];
			
		return $key;
	}

}
