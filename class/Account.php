<?php

if ( !defined ( "FROM_INDEX" ) )
	die ( "Stop trying to hack the system!" );

if ( !class_exists ( "LoginFailException" ) )
	require "LoginFailException.php";

if ( !class_exists ( "RegisterFailException" ) )
	require "RegisterFailException.php";


interface iAccount {

	/**
	 * isUserLoggedIn
	 * Checks to see if the current user is logged in
	 *
	 * @return boolean
	**/
	public function isUserLoggedIn ( );

	/**
	 * getClasses
	 * Returns an array of the user classes, if they're logged in
	 *
	 * @return array
	**/
	public function getClasses ( );

	/**
	 * getEmail
	 * Returns the users current email, if they're logged in
	 *
	 * @return string
	**/
	public function getEmail ( );

	/**
	 * getName
	 * Gets the current users name, if they're logged in
	 *
	 * @return string
	**/
	public function getName ( );

	/**
	 * getUID
	 * Gets the current users unique ID
	 *
	 * @return int
	**/
	public function getUID ( );

	/**
	 * getPasswordHash
	 * Returns the users password in an sha256 hash
	 *
	 * @return string
	**/
	public function getPasswordHash ( );

	/**
	 * getRank
	 * Returns the users rank (permission set)
	 *
	 * @return int
	**/
	public function getRank ( );

	/**
	 * getRankAsString
	 * Returns the users rank as a human string (permission set)
	 *
	 * @return string
	**/
	public function getRankAsString ( );

	/**
	 * setCurrentName
	 * Sets the name of the current user
	 *
	 * @param	string		$name		The new name for the user
	 * @return void
	**/
	public function setCurrentName ( $name );

	/**
	 * updateCurrentPassword
	 * Updates the password for the current user
	 *
	 * @param	string		$pass		The new password for the user
	 * @return void
	**/
	public function updateCurrentPassword ( $pass );

	/**
	 * logIntoAccount
	 * Tries to start a new session and set the current user
	 * If the login fails, throws the 'LoginFailException' exception
	 *
	 * @param	string		$email		The account email to try to log into
	 * @param	string		$password 	The password for the account to log into
	 * @throws	LoginFailException
	 * @return boolean
	**/
	public function loginToAccount ( $email, $password );

	/**
	 * registerAccount
	 * Registers an account to the 'tmp_users' table
	 * Once a user is registered, they will get a confirmation email
	 *
	 * @param	string		$email		The email to regsiter
	 * @param string		$password	The password to register for the email
	 * @return boolean
	**/
	public function registerAccount ( $email, $password );

	/**
	 * sendAccountConfirmation
	 * Sends an account confirmation email. The email must be registered
	 * from the 'tmp_users' table. This is called after 'Account::registerAccount'
	 *
	 * @param	string		$email			The email to check and send the conrfirmation to
	 * @return boolean
	**/
	public function sendAccountConfirmation ( $email );

	/**
	 * activateTmpAccount
	 * Activates a temparery account by moving all data for the account
	 * from 'tmp_users' to 'users'
	 *
	 * @param	int			$id			The temperary ID to check and activate
	 * @return boolean
	**/
	public function activateTempAccount ( $id );

	/**
	 * getAccountFromEmail
	 * Returns account array from an email
	 *
	 * @param	string		$email		The email to get account information for
	 * @return array
	**/
	public function getAccountFromEmail ( $email );

	/**
	 * deleteAccount
	 * Completely deletes an account from every table
	 *
	 * @param	int			$id			The account ID to delete
	 * @return boolean
	**/
	public function deleteAccount ( $id );
}


class Account implements iAccount {

	// $mRanks - Converts integer ranks to string ranks
	public $mRanks = array (
		0 => "User",
		1 => "Admin"
	);

	// Accoutn variables
	private $password;
	protected $email, $name, $uid, $classes, $rank;
	public $isUserLoggedIn = false;

	/*private $classes_ = array (
		"English"=>array(
			array ( "title"=>"Paper", "due_date"=>"9/10/2015", "status"=>"incomplete" ),
			array ( "title"=>"Another paper", "due_date"=>"9/10/2015", "status"=>"complete" )
		),

		"History"=>array(
			array("title"=>"Current Events #1", "due_date"=>"9/3/2015", "status"=>"started" )
		)
	);*/

	// Used to store all accounts
	private $accounts = array ( );


	// $Account::__construct
	public function __construct ( ) {

		// Get database connection
		global $Database;

		// Query all users
		$q = $Database->query ( "SELECT * FROM users" );

		// Validate result
		if ( $q->num_rows > 0 ){

			// Add all uers to $accounts array
			while ( $row = $q->fetch_array ( ) ) {
				$this->accounts[strtolower($row['email'])] = $row;
				$this->accounts[strtolower($row['email'])]['classes'] = array ( );

				foreach ( json_decode ( $row['classes'] ) as $index=>$name )
					$this->accounts[strtolower($row['email'])]['classes'][$name] = array ( );

			}
		}


		// Check if user is logged in
		if ( isset (
			$_SESSION,
			$_SESSION['email'],
			$_SESSION['password']
		) ) {
			// User potentially valid login - validate

			$email = $_SESSION['email'];
			$pass = $_SESSION['password'];

			// Make sure account exists
			if ( isset ( $this->accounts[strtolower($email)])) {

				// Make sure passwords match
				$account = $this->accounts[strtolower($email)];
				if ( $pass == $account['password'] ){

					// User is logged in - set account variables
					$_account = $this->accounts[strtolower($email)];
					$this->isUserLoggedIn = true;
					$this->email = $email;
					$this->name = $_account['name'];
					$this->uid = $_account['uid'];
					$this->classes = $_account["classes"];
					$this->password = $pass;
					$this->rank = $account['urank'];

					// Class sorting cookies
					if ( !isset ( $_COOKIE['sortClasses'] ) )
						setcookie ( 'sortClasses', 'byClassName', 9999999999 );

					// Methods of how to sort the classes
					$classSortMethods=array(
						"byCreation"		=> function( $classes ) { return $classes; },											// Sort classes by creation (Ascending)
						"byCreationDESC"	=> function( $classes ) { return array_reverse ( $classes ); },							// Sort classes by creation (Descending)
						"byClassName"		=> function( $classes ) { ksort ( $classes ); return $classes; },						// Sort classes by name (A-Z)
						"byClassNameDESC"	=> function( $classes ) { ksort ( $classes ); return array_reverse ( $classes ); },		// Sort classes by name (Z-A)
					);

					// Call appropriate function to sort classes
					if ( isset ( $classSortMethods[$_COOKIE['sortClasses']] ) ) {
						$this->classes = $classSortMethods[$_COOKIE['sortClasses']] ( $this->classes );
					} else {
						setcookie ( "sortClasses", "byClassName", 9999999999 );
						$this->classes = $classSortMethods["byClassName"] ( $this->classes );
					}


					// assignment sorting cookies
					if ( !isset ( $_COOKIE['sortAssignments'] ) )
						setcookie ( 'sortAssignments', 'byAssignmentName', 9999999999 );

					// assignment sort query updates
					$assignmentSortQueryString = "";
					$assignmentSortMethods=array(
						"byCreation" 				=> "",														// Order assignments by creation (Ascending)
						"byCreationDESC" 			=> "DESC",													// Order assignments by creation (Desceding)
						"byAssignmentName" 			=> "ORDER BY assignmentName",								// Order assignments by name (A-Z)
						"byAssignmentNameDESC" 		=> "ORDER BY assignmentName DESC",							// Order assignments by name (Z-A)
						"byAssignmentStatus" 		=> "ORDER BY assignmentStatus DESC, assignmentName",		// Order assignments by assignment status (complete-incomplete)
						"byAssignmentStatusDESC" 	=> "ORDER BY assignmentStatus, assignmentName",				// Order assignments by assignment status (incomplete-complete)
						"byDueDate" 				=> "ORDER BY _dueDate, assignmentName",						// Order assignments by due date (first-last)
						"byDueDateDESC" 			=> "ORDER BY _dueDate DESC, assignmentName",				// Order assignments by due date (last-first)
					);

					// Call appropriate function to sort classes
					if ( isset ( $assignmentSortMethods[$_COOKIE['sortAssignments']] ) ) {
						$assignmentSortQueryString = $assignmentSortMethods[$_COOKIE['sortAssignments']];
					} else {
						setcookie ( "sortAssignments", "byAssignmentName", 9999999999 );
						$assignmentSortQueryString = $assignmentSortMethods["byAssignmentName"];
					}


					// Get user assignments
					$q = $Database->query (
						"SELECT
							uid,
							assignmentName,
							dueDate AS '_dueDate',
							DATE_FORMAT(dueDate,'%d %M, %Y') AS 'dueDate',

							CASE assignmentStatus
								WHEN 0 THEN 'incomplete'
								WHEN 1 THEN 'complete'
								WHEN 2 THEN 'in progress'
								ELSE 'incomplete'
							END AS 'assignmentStatus',

							forClass,
							forUser,
							DATEDIFF(dueDate,DATE_SUB(SYSDATE(), INTERVAL 7 HOUR)) AS 'remainingDays'
						FROM assignments WHERE
							forUser={$this->uid}
						$assignmentSortQueryString;"
					);

					// Validate assignment query & insert to proper array
					if ( $q && $q->num_rows > 0) {
						while ( $row = $q->fetch_array() ) {
							$tmp = array (
								"title"=>$row['assignmentName'],
								"status"=>$row['assignmentStatus'],
								"due_date"=>$row["dueDate"],
								"uid"=>$row['uid'],
								"remaining"=>$row["remainingDays"]
							);
							$this->classes[$row["forClass"]][] = $tmp;
						}
					}
				}
			}
		}
	}


	// Check if the use ris logged in
	public function isUserLoggedIn ( ) {
		return $this->isUserLoggedIn;
	}


	/**
	* Getter functions
	**/
	// Get user email
	public function getEmail ( ) {
		if ( $this->isUserLoggedIn() )
			return $this->email;
	}

	// Get user name
	public function getName ( ) {
		if ( $this->isUserLoggedIn() )
			return $this->name;
	}

	// Get user ID
	public function getUID ( ) {
		if ( $this->isUserLoggedIn() )
			return $this->uid;
	}

	// Get user password hash
	public function getPasswordHash ( ){
		if ( $this->isUserLoggedIn() )
			return $this->password;
	}

	// Get user rank (int)
	public function getRank ( ) {
		if ( $this->isUserLoggedIn() )
			return $this->rank;
	}

	// Get user classes array
	public function getClasses() {
		if ( $this->isUserLoggedIn() )
			return (array)$this->classes;
	}

	// Get user rank as a string
	public function getRankAsString ( ) {
		if ( $this->isUserLoggedIn() ) {
			// Make sure the user rank exists in the mRanks array
			if ( !isset ( $this->mRanks [ $this->rank ] ) )
				return "????";
			else
				return $this->mRanks [ $this->rank ];
		}
	}

	/***********
	* Setter functions
	***********/
	// Set the name of the user
	public function setCurrentName ( $name ) {
		global $Database;
		$str = $Database->escape ( $name );
		$Database->query ( "UPDATE users SET name='{$str}' WHERE uid='{$this->uid}'" );
		$this->name = $name;
	}

	// Update the user password
	public function updateCurrentPassword ( $pass ) {
		global $Database;

		$sHash = hash ( "sha256", $pass );

		if( $prep = $Database->connection->prepare("UPDATE users SET password=? WHERE uid='{$this->uid}'") ){
			$prep->bind_param("s", $sHash);
			$prep->execute();

			$this->password = $sHash;
			$_SESSION['password'] = $sHash;
		}

	}


	/**
	* Account login
	**/
	public function loginToAccount ( $email, $password ) {

		// Check if variables are set
		if ( !isset ( $email, $password ) )
			throw new LoginFailException ( "Expected 2 arguments: email, password" );

		// Check if the user is logged in
		if ( $this->isUserLoggedIn ( ) )
			throw new LoginFailException ( "Server error: You're already logged in" );

		// Convert the email to lower-case
		$email = strtolower ( $email );

		// Check if the account already exists
		if ( isset ( $this->accounts[$email] ) ) {

			// User exists - Try login
			$account = $this->accounts[$email];

			// Check if the account is active
			if ( $account['active'] == 1 ) {

				// Check if the passwords match
				if ( hash ( "sha256", $password ) == $account['password'] ) {

					global $Database;

					// Set session information
					$_SESSION['email'] = $email;
					$_SESSION['uid'] = $account['uid'];
					$_SESSION['password'] = hash ( "sha256", $password );

					// Update last_login database column
					$Database->query("UPDATE users SET last_login=DATE_SUB(NOW(), INTERVAL 7 HOUR) WHERE uid={$account['uid']}");


					return true;
				} else
					// Password doesn't match
					throw new LoginFailException ( "Either your email or password is incorrect" );
			} else
				// Account not active
				throw new LoginFailException ( "This account is not yet active, open your email to activate it - <a href='index.php?p=resendConf'>Resend</a>" );
		} else
			// Account doesn't exist
			throw new LoginFailException ( "Either your email or password is incorrect" );

		// Some other error?
		throw new LoginFailException ( "Sorry but we're not sure what went wrong trying to login." );

	}


	/**
	* Account Registration
	**/
	public function registerAccount ( $email, $password, $grade=0 ) {
		global $Database;

		$email = strtolower ( $email );

		// Check current database to see if it's already registered
		if ( isset ( $this->accounts [ strtolower ( $email ) ] ) )
			throw new RegisterFailException ( "This account is already in use" );

		// All good - Create inactive account
		require "Utilities.php";

		$hPassword = hash ( "sha256", $password );
		$randHash = Utilities::randomString ( 20 );

		// Try to decode email to get name
		$name = 'New User';
		if ( count ( explode ( ".", $email ) ) == 3 ) {
			$explodedEmail1 = explode ( ".", $email );
			if ( count ( explode ( "@", $explodedEmail1[1] ) ) == 2 ){
				$name = ucfirst ( $explodedEmail1[0] )." ".ucfirst ( explode ( "@", $explodedEmail1[1] ) [ 0 ] );
			}
		}

		// Prevent sql injection
		$email = $Database->escape ( $email );
		$hPassword = $Database->escape ( $hPassword );

		// Insert account to database
		$Database->query ( "
			INSERT INTO users ( email, password, classes, name, urank, active, activation_key, registered_at, last_login ) VALUES(
				'$email', '$hPassword', '[ ]', '$name', 0, 0, '$randHash', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 HOUR), DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 HOUR)
			);" );

		// Send email activation link to confirm account
		$this->sendAccountConfirmation( $email );

		return true;
	}


	// Send activation link to email
	public function sendAccountConfirmation ( $email ){
		global $Database;

		// Make sure the account is inactive and exists
		$dQuery = $Database->query ( "SELECT uid, activation_key FROM users WHERE LOWER(email)=LOWER('{$email}') AND active=0 LIMIT 1" );

		if ( !$dQuery || $dQuery->num_rows == 0 )
			return false;

		$result = mysqli_fetch_array ( $dQuery );


		// Get activation key and id from database query
		$code = $result['activation_key'];
		$uid = $result["uid"];

		// Set email headers
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html;' . "\r\n";
		$headers .= "From:no-reply@braydond.smtchs.org" . " \r\n";
		$message = "Hello there!<br/><br/>Thank you for registering on the MTCHS Student Work Tracker. Your account has been registered, now the last step is to activate it!<br/><br/>To activate your account, please go to the following address:<br/>http://braydond.smtchs.org/work_tracker/index.php?p=confirm&key={$code}&uid={$uid}";

		// Send email
		return mail ( $email, "MTCHS Student Work Tracker", $message, $headers );

	}

	// Used to activate an account
	public function activateTempAccount ( $id ) {

		global $Database;

		// Make sure the account exists
		$query = $Database->query ( "SELECT * FROM users WHERE uid=$id AND active=0" );
		if ( $query && $query->num_rows > 0 ) {

			// Activate the account
			$Database->query ( "UPDATE users SET active=1, activation_key=NULL WHERE uid=$id;" );
			return true;
		}

		// echo "Something went wrong";
		return false;
	}

	// Get an account from an email
	public function getAccountFromEmail ( $email ) {
		return isset ( $this->accounts [ strtolower ( $email ) ] ) ? $this->accounts [ strtolower ( $email ) ] : false;
	}

	// Delete account along with all connected information
	public function deleteAccount ( $id ) {
		global $Database;
		// Delete account
		$Database->query("DELETE FROM users WHERE uid=$id;");
		// Delete assignments
		$Database->query("DELETE FROM assignments WHERE forUser=$id;");
		// Delete password recoveries
		$Database->query("DELETE FROM password_recover_keys WHERE uid=$id");

		return true;
	}

	// Used to add a class to the classes array
	public function addClassToArray ( $className ) {

		foreach ( $this->classes as $index=>$val ){
			if ( strtolower ( $index ) == strtolower ( $className ) )
				return false;
		}

		$this->classes[$className] = array( );
	}

	// Used to remove a class from the classes array
	public function removeClassFromArray ( $className ){
		if ( isset ( $this->classes[$className ] ) )
			unset ( $this->classes[$className] );
	}
}

// Create account instance
$Account = new Account ( );

?>
