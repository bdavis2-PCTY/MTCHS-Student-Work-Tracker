<?php


	// Prevent hackers
	if ( !defined ( "FROM_INDEX" ) )
		die ( "Stop trying to hack the system!" );

	// Database class
	class Database {
		// Database connection info
		private $host, $user, $pass, $db;

		// Is the database connected?
		private $connected = false;

		// The database connection
		public $connection;

		// Database constructor
		public function __construct ( $host, $user, $pass, $db ) {
			// Set class connection info members
			$this->host = $host;
			$this->user = $user;
			$this->pass = $pass;
			$this->db = $db;

			// Try to connect with the given information
			$this->connection = @new mysqli ( $host, $user, $pass, $db );


			// Check if the connection was a success
			if ( $this->connection && !$this->connection->errno )
				// Connection successful
				$this->connected = true;
			else {
				// Connection errored - notify admins
				mail("braydon.davis@mtchs.org","MTCHS STUDENT WORK ERROR", "Database error: " . mysql_error() ."\n\nFix as soon as possible!");
				die ( "<h1>ERROR ESTABLISHING DATABASE CONNECTION</h1><br/>Please try again later, a developer has been notified!" );
			}

			// Return the connection
			return $this->connection;
		}

		// Execute a database query
		public function query ( $str ) {
			if ( !$this->connected )
				return false;

			return $this->connection->query ( $str );
		}

		// Escape a string for save querying
		public function escape ( $str ) {
			return $this->connection->real_escape_string  ( $str );
		}
	}

	// connect to the database
	$Database = new Database ( "localhost", "braydond_worktrk", "WorkTrackerPassword123", "braydond_work_tk" );

	// Update mysql timezone
	$Database->query("SET time_zone='UTC-7';");

	// Create users table
	$Database->query ( "CREATE TABLE IF NOT EXISTS `users` (
	  `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique user ID - Auto increment',
	  `email` varchar(300) NOT NULL COMMENT 'User email address',
	  `password` varchar(300) NOT NULL COMMENT 'User login password',
	  `classes` text NOT NULL COMMENT 'User classes - JSON string',
	  `name` varchar(300) NOT NULL COMMENT 'User name',
	  `urank` varchar(50) NOT NULL COMMENT 'User rank',
	  `active` int(2) NOT NULL COMMENT 'Is account activated',
	  `activation_key` varchar(200) DEFAULT NULL COMMENT 'Account activation key',
	  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Account registration date and time',
	  `last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Last account login date and time',
	  PRIMARY KEY (`uid`),
	  UNIQUE KEY `uid` (`uid`,`email`) )"
		);

	// Create assignments table
	$Database->query ( "CREATE TABLE IF NOT EXISTS `assignments` (
		`uid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Unique assignment ID',
		`assignmentName` varchar(300) NOT NULL COMMENT 'Assignment name',
		`dueDate` date NOT NULL COMMENT 'Assignment due date',
		`assignmentStatus` int(2) NOT NULL COMMENT 'Assignment status',
		`forClass` varchar(300) NOT NULL COMMENT 'Assignment parent class',
		`forUser` int(11) NOT NULL COMMENT 'Assignment parent user ID',
		PRIMARY KEY (`uid`),
		UNIQUE KEY `uid` (`uid`)
	)" );

	// Create forgotten password table
	$Database->query ( "CREATE TABLE IF NOT EXISTS `password_recover_keys` (
		`uid` int(11) NOT NULL COMMENT 'Account ID',
		`recoverKey` varchar(50) NOT NULL COMMENT 'Recover key',
		`added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Time user forgot',
		UNIQUE KEY `uid` (`uid`)
	);");

	// Create trigger to delete old, unactive users
	@$Database->query ( "CREATE TRIGGER `DeleteOldUsers` AFTER INSERT ON `assignments`
		FOR EACH ROW DELETE FROM users WHERE active=0 AND DATEDIFF(SYSDATE(),registered_at) >= 2" );

	// Create trigger to delete old password recovery keys
	@$Database->query ( "CREATE TRIGGER `DeleteOldPasswordRecovery` BEFORE INSERT ON `assignments`
		FOR EACH ROW DELETE FROM password_recover_keys WHERE DATEDIFF(SYSDATE(),added_at) >= 2" );





	// Old table creation
	#$Database->query ( "CREATE TABLE IF NOT EXISTS users ( uid INT, email VARCHAR(300), password VARCHAR(300), classes TEXT, name VARCHAR(300), urank VARCHAR(50) )");
	#$Database->query ( "CREATE TABLE IF NOT EXISTS assignments ( uid INT, assignmentName VARCHAR(200), dueDate DATE, assignmentStatus INT, forClass VARCHAR(300), forUser INT )");
	#$Database->query ( "CREATE TABLE IF NOT EXISTS tmp_users ( uid INT, email VARCHAR(300), password VARCHAR(300), grade_lvl INT(2), registered_at DATE, conf_code VARCHAR(50) )" );
	#$Database->query ( "INSERT INTO assignments ( uid, assignmentName, dueDate, assignmentStatus, forClass, forUser ) VALUES ( '65626', 'Sample Assignment 1', '2015-09-12', '0', 'English', '52623' );");
