<?php 

	if ( !defined ( "FROM_INDEX" ) ) 
		die ( "Stop trying to hack the system!" );

class LoginFailException extends Exception { 
	
	public function __construct ( $str="" ) {
		parent::__construct ( $str );
	}
	
}