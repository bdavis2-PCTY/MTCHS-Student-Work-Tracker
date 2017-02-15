<?php 

	if ( !defined ( "FROM_INDEX" ) ) 
		die ( "Stop trying to hack the system!" );

if ( !class_exists ( "RegisterFailException" ) ) {
	class RegisterFailException extends Exception { 
		
		public function __construct ( $str="" ) {
			parent::__construct ( $str );
		}
		
	}
}