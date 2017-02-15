<?php

	define ( "FROM_INDEX", "true" );		// Checked in other files to prevent direct links
	define ( "DEVELOPMENT_MODE", "false" );	// When set to 'true' only admins can access the site

	/* Begin user session if it doesn't exist */
	if ( session_status() == PHP_SESSION_NONE)
		session_start();

	// Check for user logout
	if ( isset ( $_GET['forceLogOut'] ) ){
		session_destroy();
		session_start();
		die ( "<script>window.location='index.php';</script>" );
	}

	$includes = array ( );	// Used to determine what files need to be loaded
	$messages = array ( );	// Used to send messages to the files that are loaded
	$pageVars = array ( );	// Used to store variables for files that are loaded

	//  Use multiple message arrays for different sections on the same page
	/// Eg. new assignment has new classes too and needs its own notification area
	for ( $i = 0; $i < 5; $i++ )
		$messages [ $i ] = array ( );


	// Global include files
	require "class/Database.php";
	require "class/Account.php";

	// Used to refresh the page/send to main page
	function sendHome ( ) {
		echo "<script type='text/javascript'>window.location='index.php';</script>";
	}

	// Check if the site is in development mode
	if ( DEVELOPMENT_MODE == "true" ){
		// Check if the user is an admin
		if ( !$Account->isUserLoggedIn() || $Account->getRank ( ) == 0 ){
			// Not an admin, display development message and die ( );
			require "header.php";
			echo "<br/><br/><p style='padding:20px;font-size:25px;'>The site is currently under development, please come again later!</p><br/><br/>";
			require "footer.php";
			die ( );
		}
	}

	/*******
	* PAGES WHEN THE USER IS NOT LOGGED IN
	*******/
	if ( !$Account->isUserLoggedIn() ) {

		// Determine what page needs to be loaded (default: login)
		$p = 'login';
		if ( isset ( $_GET['p'] ) )
			$p = strtolower ( $_GET['p'] );

		switch ( $p ) {






			/*******
			* Login page
			*******/
			case "login":
				$message = "";

				// Check for user login
				if ( !isset ( $Account ) || $Account->isUserLoggedIn() )
					die ( "Stop trying to hack the system!!" );

				// Check if they've submitted login credentials
				if ( isset ( $_POST['login_email'], $_POST['login_password'] ) ) {

					// Try to login
					try {

						// Attempt login
						if ( $Account->loginToAccount ( $_POST['login_email'], $_POST['login_password'] ) ){

							// Login success - set stay logged in cookie
							if ( isset ($_POST['login_stay_loggedIn'] ) && $_POST['login_stay_loggedIn'] == "stayLoggedIn" )
								session_set_cookie_params(9999999999999);

							// Success message
							$message = "<span class='label label-success'>Valid login,  you will be redirected shortly.</span><script>window.location=window.location</script><br/><br />";
						}else{
							// Throw login error
							throw new LoginFailException ( "Unable to login" );
						}
					// Login failed
					} catch ( LoginFailException $e ) {
						// Login error message
						$message = "<span class='label label-danger'>{$e->getMessage()}</span><br /><br />";
					}

				}

				// Include login view
				$includes[0] = "pages/login.php";
				break;









			/*******
			* Register page
			*******/
			case "register":

					/**
						* Requirements
						* - Valid email
						* - Email and confirm email match
						* - Email is @mtchs.org domain
						* - Password and password confirm match
						* - Password is at lest 6 characters long
					**/

					// Check if the form was submitted
					$pageVars['registerPageStep'] = 1;
					if ( isset ( $_POST['registerForm'] ) ) {
						require "class/RegisterFailException.php";
						// Get all data from registration form
						$email = strtolower ( $_POST['register_email'] );
						$emailConf = strtolower( $_POST['register_email_conf'] );
						$password = $_POST['register_password'];
						$passwordConf = $_POST['register_password_conf'];
						$grade = @(int)$_POST['register_gradeLevel'];

						try {
							// Check valid email
							if ( !filter_var ( $email, FILTER_VALIDATE_EMAIL ) )
								throw new RegisterFailException ( "Please enter a valid email adress" );

							// Confirm emails
							if ( $email != $emailConf )
								throw new RegisterFailException ( "Your email adresses don't match" );

							// Check @mtchs.org
							$tmpEmailSplit = explode ( "@", $email );
							if ( count ( $tmpEmailSplit ) <= 0 || $tmpEmailSplit[1] != "mtchs.org" )
								throw new RegisterFailException ( "You need to have an @mtchs.org email" );

							// Make sure password is 6+ chars
							if ( strlen ( $password ) < 6 )
								throw new RegisterFailException ( "Password must be at least 6 characters" );

							// Make sure passwords match
							if ( $password != $passwordConf )
								throw new RegisterFailException ( "Your passwords don't match" );

							// Actually attmept to register account
							$Account->registerAccount ( $email, $password, $grade );

							//$messages[0][] = "<span class='label label-success' style='font-size:12px;'>Your account has been created but not activated!</span><br/><span class='label label-success' style='font-size:12px;'>To activate your account, please check your email. You have 2 days to confirm it or you will need to register again.</span><br />";
							$pageVars['registerPageStep'] = 2;
							// Registration success

						// Registration failed
						} catch ( RegisterFailException $e ) {
							// Display error message
							$messages[0][] = "<span class='label label-danger' style='font-size:12px;'>{$e->getMessage()}</span><br />";
						}

				}

				// Load login view
				$includes[] = "pages/register.php";
				break;








			/*******
			* Account confirmation
			*******/
			case "confirm":
				// Make sure our variables exist that we need
				// uid: account id
				// key: Confirmation code

				// Check to see if its the valid url
				if ( isset ( $_GET["uid"], $_GET['key'] ) ){

					// Get the $_GET vars
					//$accId: The account ID to activate
					// $key: the activation key

					$accId = intval ( $_GET['uid'] );
					$key = $_GET['key'];


					global $Database;
					// Make sure it's valid information in the database
					$query = $Database->query ( "SELECT activation_key FROM users WHERE uid=$accId AND active=0" );

					// Make sure we got a valid result
					$qResult = $query->fetch_array ( );
					if ( $query && $qResult ) {

						// Check if $key is the same as the activation key in the database
						if ( $qResult['activation_key'] == $key ) {

							// Keys are valid - activate the account
							echo "<div id='usersMainContentArea'>Your account has been activated. You can now login.<br/><br/>
					<a href='index.php'><input type='button' value='Back' class='btn btn-primary' /></a></div>";
							$Account->activateTempAccount ( $accId );

						// Invalid key
						} else {
							// Invalid confirmation code
							echo "<div id='usersMainContentArea'>Invalid access key<br/><br/>
					<a href='index.php'><input type='button' value='Back' class='btn btn-primary' /></a></div>";
						}

					// Invalid account ID
					} else {
						echo "<div id='usersMainContentArea'>Invalid account id<br/><br/>
					<a href='index.php'><input type='button' value='Back' class='btn btn-primary' /></a></div>";
					}
					break;
				}








			/*******
			* Forgot password page
			*******/
			case "forgotpassword":


				// Declare which step of the process we're at
				$pageVars['forgotPassStep'] = 1;

				// Check if user has submitted email address
				if ( isset ( $_POST['continueStepOne'], $_POST['forgot_email'] ) ) {
					$recoverEmail = strtolower ( $Database->escape ( $_POST['forgot_email'] ) ); # Get email to recover

					// Validate email
					if ( FILTER_VAR ( $recoverEmail,FILTER_VALIDATE_EMAIL ) ) {

						// Check to see if the email is registered
						$account = $Account->getAccountFromEmail ( $recoverEmail );
						if ( $account ) {
							require "class/Utilities.php";

							// Get user account id
							$id = $account['uid'];

							// Update to step 2
							$pageVars['forgotPassStep'] = 2;

							// Trick the system into thinking its in step one
							$_POST['continueStepOne'] = null;
							$_POST['forgot_email'] = null;

							// Generate random recovery key
							$key = Utilities::randomString(20);

							// Update old values
							$Database->query("DELETE FROM password_recover_keys WHERE uid=$id;");
							$Database->query("INSERT INTO password_recover_keys (uid, recoverKey, added_at) VALUES ($id, '$key', DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 7 HOUR) );");

							// Send confirmation email
							$message = "Hello {$account['name']},\n\nSomeone has requested to get your password reset for the MTCHS Student Work Tracker. If this wasn't you, don't worry, you're safe! Howver, if it was you, please navigate to the following link to recover your account:\nhttp://braydond.smtchs.org/work_tracker/index.php?p=forgotPassword&a=$id&key=$key\n\nIf you have any problems or questions, feel free to email Braydon Davis <braydon.davis@mtchs.org>.\nThank you!";
							mail ( $account['email'], "MTCHS Student Work Tracker", $message, "From: MTCHS Student Work Tracker <no-reply@braydond.smtchs.org>" );

						// Invalid account
						} else
							$messages[0][] = "<span class='label label-danger'>That email address is not registered</span>";

					// Invalid email address format
					} else
						$messages[0][] = "<span class='label label-danger'>Please enter a valid email address</span>";
				}

				// Check if user is recovering
				if ( isset ( $_GET['a'], $_GET['key'] ) ) {
					// Declare our info
					$pageVars['forgotPassStep'] = 3;
					$pageVars['forgotPassID'] = (int)$Database->escape ( $_GET['a'] );
					$pageVars['forgotPassKey'] = $Database->escape ( $_GET['key'] );

					// Validate key
					$passRecQuery = $Database->query ( "SELECT * FROM  `password_recover_keys` WHERE `uid`={$pageVars['forgotPassID']} AND `recoverKey`='{$pageVars['forgotPassKey']}' LIMIT 1" );

					if ( $passRecQuery->num_rows == 1 ) {
						// Check if they've already udpated their password
						if ( isset ( $_POST['btnUpdatePassword'], $_POST['forgotPassNewPass'], $_POST['forgotPassNewPassConf'] ) ) {
							$newPass = $_POST['forgotPassNewPass'];

							// Validate password
							if ( trim ( $newPass ) != "" ) {
								if ( $newPass == $_POST['forgotPassNewPassConf'] ) {
									if ( strlen ( $newPass ) >= 6 ) {
										// Password is valid - update it
										$nPassHash = hash ( "sha256", $newPass );
										$Database->query("UPDATE `users` SET `password`='$nPassHash' WHERE `uid`={$pageVars['forgotPassID']}");
										$pageVars['forgotPassStep'] = 4;
										$Database->query ( "DELETE FROM `password_recover_keys` WHERE `uid`={$pageVars['forgotPassID']}" );
										//$messages[1][] = "<spaYour password has been updated! You can now <a href='index.php'>Login</a> with your new credentials.";

									// Password not long enough
									} else
										$messages[1][] = "<span class='label label-danger'>Password must be at least 6 characters</span>";

								// Passwords don't match
								} else
									$messages[1][] = "<span class='label label-danger'>Passwords do not match</span>";

							// No password
							} else
								$messages[1][] = "<span class='label label-danger'>Password must contain characters</span>";
						}

					// Invalid recovery key - send home
					} else {
						header ( "Location:index.php" );
						die ( );
					}
				}

				// Include forgot password view
				$includes[] = "pages/forgot_password.php";
				break;


			/*******
			* Resend confirmation email
			*******/
			case "resendconf":
				$includes[] = "pages/resendConf.php";
				break;

			default:
				echo "<script>window.location='index.php';</script>";
				return;
		}





	/*******
	* PAGES WHEN THE USER IS LOGGED IN
	*******/
	}else{
		// User is logged in
		// Determine what page to display (Default: main_view)
		$page = "main_view.php";

		if ( isset ( $_GET['p'] ) ) {
			switch ( $_GET['p'] ) {




				/*******
				* MY PROFILE
				*******/
				case "myProfile":
					// UPDATE NAME
					if ( isset ( $_POST['updateGeneral'], $_POST['usrName'], $_POST['usrConfCurrentPassGnrl'] ) ) {
						// User is trying to update general information
						$name = $_POST['usrName'];
						$confPass = $_POST['usrConfCurrentPassGnrl'];
						#Make sure user confirmed password is correct
						if ( hash ( "sha256", $confPass ) == $Account->getPasswordHash() ) {
							#Make sure that user name is somewhat valid
							if ( strlen ( $name ) > 5 && preg_match("/^[a-zA-Z ]*$/",$name ) ) {
								#All valid - update the profile
								$Account->setCurrentName($name);
								$messages[0][] = "<span class='label label-success'>Your profile has been successfully updated!</span>";

							// Password not long enough
							} else
								$messages[0][] = "<span class='label label-danger'>Your name must be at least 6 characters of only letters & spaces</span>";

						// Current password doesn't matter
						} else
							$messages[0][] = "<span class='label label-danger'>Sorry, the password you entered doesn't match your current one</span>";

					// CHANGE PASSWORD
					} elseif ( isset ( $_POST['updatePassword'], $_POST['usrNewPassword'], $_POST['usrConfNewPassword'], $_POST['usrConfCurrentPassChng'] ) ) {
						// Get form information
						$newPass = $_POST['usrNewPassword'];
						$confNew = $_POST['usrConfNewPassword'];
						$confOld = $_POST['usrConfCurrentPassChng'];
						#Make sure user confirmed password is correct
						if ( hash ( "sha256", $confOld ) == $Account->getPasswordHash() ) {
							// Check if password and confirm password match
							if ( $confNew == $newPass ) {
								// Check if the password is long enough
								if ( strlen ( $newPass ) > 6) {
									// Update the password
									$Account->updateCurrentPassword($newPass);
									$messages[1][] = "<span class='label label-success'>Your password has been updated. You may need to log back in.</span>";

								// Password not long enough
								} else
									$messages[1][] = "<span class='label label-danger'>Your new password must be at least 7 characters</span>";

							// Passwords don't match
							} else
								$messages[1][] = "<span class='label label-danger'>Your new passwords don't match</span>";

						// Current password doesn't match
						} else
							$messages[1][] = "<span class='label label-danger'>Sorry, the password you entered doesn't match your current one</span>";


					// VIEWING OPTIONS
					} elseif ( isset ( $_POST['updateViewSettings'] ) ) {
						// Update classes sort cookie
						if ( isset ( $_POST['viewSortClasses'] ) )
							setcookie ( "sortClasses", $_POST['viewSortClasses'], 9999999999 );
						// Update assignments sort cookie
						if ( isset ( $_POST['viewSortAssignments'] ) )
							setcookie ( "sortAssignments", $_POST['viewSortAssignments'], 9999999999 );

						$messages[2][] = "<span class='label label-success'>Your changes have been saved</span>";

					}

					// Include "my profile" view
					$includes[] = "pages/my_profile.php";
					break;






				/*******
				* New assignment page
				*******/
				case "newAssignment":

					// Check if they've submitted information
					if ( isset ( $_POST['addNewAssignment'],
					$_POST['newAssignmentName'],
					$_POST['newAssignmentDue'],
					$_POST['newAssignmentClass'] ) ) {

						// Get submitted information
						$name = ( $_POST['newAssignmentName'] );
						$due =  ( $_POST['newAssignmentDue'] );
						$class =  ( $_POST['newAssignmentClass'] );

						// Validate the date format (YYYY-MM-DD)
						$dueNums = explode ( "-", $due );

						// Name sure name only contains a-Z, 0-9, (), -
						if ( preg_match("/^[a-zA-Z0-9\s()-]+$/", $name ) ){

							// Confirm there's a day month and year
							if ( count ( $dueNums ) == 3 ) {

								// Convert the numbers from strings to integers
								$year = @intval ( $dueNums [ 0 ] );
								$month = @intval ( $dueNums [ 1 ] );
								$day = @intval ( $dueNums [ 2 ] );

								// Confirm the numbers could be converted to integers
								if ( is_integer ( $year ) && is_integer ( $month ) && is_integer ( $day ) ){
									// Check if they're valid numbers for a date
									if( $year >= 1900 && $month > 0 && $month < 13 && $day > 0 ) {

										global $Account;
										// Check if the user has the class
										if (isset($Account->getClasses()[$class])){

											// Prevent XSS & sql injection attacks
											$name = $Database->escape ( $name );
											$class = $Database->escape ( $class );
											$due = $Database->escape ( $due );

											// Make sure the assignment name isn't too long for the database....
											if ( strlen ( $name ) <= 150 ) {

												// ASSIGNMENT HAS PASSED ALL CHECKS
												// WE'RE GOOD TO ADD IT TO THE DATABASE
												$Database->query ( "INSERT INTO assignments ( assignmentName, dueDate, assignmentStatus, forClass, forUser ) VALUES ( '$name','$due','0','$class',{$Account->getUID()} )" );
												$messages[0][] = "<span class='label label-success'>Your assignment has been added!</span>";

											// Assignment name is too long
											}else
												$messages[0][] = "<span class='label label-danger'>Assignment name length is too long</span>";

										// Class isn't found
										} else
											$messages[0][] = "<span class='label label-danger'>Failed to find that class, please try again</span>";

									// Invalid date format
									} else
										$messages[0][] = "<span class='label label-danger'>Invalid date, please try again</span>";

								// Failed to convert the date
								} else
									$messages[0][] = "<span class='label label-danger'>Failed to convert the date to an integer, please try again</span>";

							// Failed to convert the date
							} else
								$messages[0][] = "<span class='label label-danger'>Something went wrong with the date, please try again</span>";

						// Illegal chars in the name
						} else
							$messages[0][] = "<span class='label label-danger'>Your class name can only consist of a-Z, 0-9, spaces, and dashes</span>";


					/*******
					* Add new class
					*******/
					} elseif ( isset ( $_POST['addNewClass'], $_POST['newClassName'] ) ) {
						// Get new class name
						$class = ( $_POST['newClassName'] );
						// Make sure the class name isn't too long
						if ( strlen ( $class ) <= 50 ) {
							// Make sure the class name doesn't contain illegal characters
							if ( preg_match("/^[a-zA-Z0-9\s()-]+$/", $class ) ){

								// Check to see if the class already exsists
								$classes = $Account->getClasses();
								if ( !isset ( $classes[$class] ) ) {
									//
									$_classes = array ( );
									foreach ( $classes as $cName=>$vars )
										$_classes[] = $cName;
									$_classes[] = $class;

									// Add classes to array for dopdown
									$Account->addClassToArray($class);

									// Convert new classes to a save JSON string
									$newClassJSON = $Database->escape ( json_encode ( $_classes ) );

									// Update classes column for the user
									$Database->query("UPDATE users SET classes='{$newClassJSON}' WHERE uid='{$Account->getUID()}'");
									$messages[1][] = "<span class='label label-success'>Your class has been added</span>";

								// Class already exists
								} else
									$messages[1][] = "<span class='label label-danger'>You already have this class</span>";

							// Class name consists illegal characters
							} else
								$messages[1][] = "<span class='label label-danger'>Your class name can only consist of a-Z, 0-9, spaces, and dashes</span>";

						// Class name is too long
						} else
							$messages[1][] = "<span class='label label-danger'>Your class name is too long</span>";


					/*********
					* Remove class
					*********/
					} elseif ( isset ( $_POST['removeClassName'], $_POST['removeClass'] ) ) {
						// Get class name
						$class = ( $_POST['removeClassName'] );

						// Check if the class actually exists
						if (isset($Account->getClasses()[$class])){

							// UPdated classes array
							$classes = $Account->getClasses();
							$_classes = array ( );
							foreach ( $classes as $cName=>$vars ){
								if ( $class != $cName )
									$_classes[] = $cName;
							}


							// Remove the class from classes array
							$Account->removeClassFromArray ( $class );

							// Convert classes to new save JSON string
							$newClassJSON = $Database->escape(json_encode($_classes));

							// Remove assignments for the old class
							$Database->query("DELETE FROM assignments WHERE forClass='{$class}' AND forUser='{$Account->getUID()}'");

							// Updated classes column for the user
							$Database->query("UPDATE users SET classes='{$newClassJSON}' WHERE uid='{$Account->getUID()}'");

							$messages[2][] = "<span class='label label-success'>This class has been removed.</span>";

						// Class doesn't exist
						}else
							$messages[2][] = "<span class='label label-danger'>You don't have that class</span>";

					}

					// Include new_assignments view
					$includes[] = "pages/new_assignment.php";
					break;






				/*******
				* Manager page (AJAX for deleting assignments by URL)
				*******/
				case "manager":

					$action = $_GET['act'];

					switch ( strtolower ( $action ) ) {

						// Delete assignment
						case "delete":
							// check if the ID exists
							if ( isset ( $_GET['id'] ) ) {

								// Does the assignment exist
								(boolean) $isAssignmentFound = false;
								foreach ( $Account->getClasses() as $class=>$assignments ) {
									foreach ( $assignments as $index=>$info ){
										if ( $info['uid'] == $_GET['id'] ){
											$isAssignmentFound = true;
											$Database->query("DELETE FROM assignments WHERE uid='{$_GET['id']}'");
											echo "<div id='usersMainContentArea'>This assignment has been deleted!<br/><br/><a href='index.php'><input type='button' value='Home' class='btn btn-primary'</a></div>";
											break;
										}
									}
								}

								if ( $isAssignmentFound ) break;
							}

						default:
							sendHome();
							break;
					}

					break;





				/*******
				* AJAX page (only called by JavaScript)
				*******/
				case "AJAX":
					if ( isset ( $_GET['ACTION'] ) ) {
						$act = $_GET['ACTION'];
						switch ( $act ) {

							// Update assignment status
							case "updateAssignmentStatus":
								// Chekc if assignmentId and toStat are set
								if ( isset ( $_GET['assignmentId'], $_GET['toStat'] ) ) {
									// Get the values as integers
									$assId = intval ( $_GET['assignmentId'] );
									$toStat = intval ( $_GET['toStat'] );

									// Check if "toStat" is valid
									if ( $toStat > 3 || $toStat < 0 ) {
										echo "0";
										return;
									}

									// Update the assignment in the database
									$query = $Database->query("UPDATE assignments SET assignmentStatus={$toStat} WHERE uid={$assId} AND forUser={$Account->getUID()}");
									echo ( (string)$query->affected_rows );
								}
								break;

							// Delete assignment
							case "deleteAssignment":
								// Check if assignmentId is set
								if ( isset ( $_GET['assignmentId'] ) ) {
									$assId = intval ( $_GET['assignmentId'] );
									// Delete the assignment
									$query = $Database->query("DELETE FROM assignments WHERE uid={$assId} AND forUser={$Account->getUID()}");
									echo ( (string)$query->affected_rows );
								} else
									echo "0";
								break;

							// Return result for side bar navigation HTML - Used for updates
							case "sideBarNavItems":
								echo "<p>Incomplete assignments</p>";
								$classes = $Account->getClasses();
								$_classes = $classes;
								foreach ( $_classes as $class=>$assignments ) {
									if ( !isset ( $class, $assignments ) || gettype ( $assignments ) != "array" || count ( $assignments ) == 0 ){
										unset ( $_classes[$class] );
										continue;
									}
									$totalAssignments = 0;
									foreach ( $assignments as $assignment=>$info ) {
										if ( $info["status"] != "complete" ){
											$totalAssignments = 1;
											break;
										}
									}
									if ( $totalAssignments == 0 )
										unset ( $_classes[$class] );
								}

								foreach ( $_classes as $class=>$assignments ) {
									if ( !isset ( $class, $assignments ) || gettype ( $assignments ) != "array" || count ( $assignments ) ==0 )
										continue;
									echo "<ul><li class='sideNavClass'>{$class}<ul>";
									foreach ( $assignments as $assignment=>$info ){
										if ( $info['status'] != "complete" ){
											$remaining = "remaining";
											echo "<li class='sidNavAssignment'>{$info['title']}<br/><em>".($info['remaining']<0?$info['remaining']*-1:$info['remaining'])." day".(($info['remaining']==1||$info['remaining']==-1)?"":"s")." ".($info['remaining']<0?"late":"remaining")."</em></li>";
										}
									}
									echo "</ul></li></ul>";
								}

								return;

							default:
								break;
						}
						echo "0";
						return false;
					}

				// Admin view stats page
				case "adminViewStats":
					if ( $Account->getRank() > 0 ){
						$includes[] = "pages/admin/viewStats.php";
						break;
					}

				// Admin view users page
				case "adminViewUsers":
					if ( $Account->getRank() > 0 ){
						$includes[] = "pages/admin/viewUsers.php";
						break;
					}

				// Admin view user class page
				case "adminViewUserClass":
					if ( $Account->getRank() > 0 ){
						$includes[] = "pages/admin/viewUserClass.php";
						break;
					}

				// Admin add user assignment
				case "adminAddUserAssignment":
					if ( $Account->getRank() > 0 ) {
						$includes[] = "pages/admin/addUserAssignment.php";
						break;
					}

				default:
					$includes[] = "pages/main_view.php";
					break;
			}
		} else {
			$includes[] = "pages/main_view.php";
		}
	}

	// include all requested files
	require "header.php";

	foreach ( $includes as $i=>$file )
		require $file;

	require "footer.php";

?>
