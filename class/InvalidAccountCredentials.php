<?php 

	if ( !defined ( "FROM_INDEX" ) ) 
		die ( "Stop trying to hack the system!" );

class InvalidAccountCredentials extends Exception { 
	
	public function __construct ( $str="" ) {
		parent::__construct ( $str );
	}
	
}