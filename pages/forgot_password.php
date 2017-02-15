<?php

if ( !defined ( "FROM_INDEX" ) )
	die ( "Stop trying to hack the system!" );

?>

<div id='forgotPasswordForm'>

	<h1>Forgot your password</h1>

	<?php
	if ( !isset ( $pageVars['forgotPassStep'] ) )
		$pageVars['forgotPassStep'] = 1;

	if ( $pageVars['forgotPassStep'] == 1 ) { ?>

	<p>Step 1: Enter your email</p>
	<br />


	<?php
		if ( isset ( $messages, $messages[0] ) && count ( $messages[0] ) > 0 ){
			foreach ( $messages[0] as $i=>$msg )
				echo $msg . " <br />";

			echo "<br />";
		}
	?>

	<form action='index.php?p=forgotPassword' method='post'>
		<label>Email:</label> <input type='email' value="<?php echo (isset($_POST['forgot_email']) ? $_POST['forgot_email'] : "" ) ?>" name="forgot_email" placeholder='Your account email address' class='form-control forgotInput' required alt='Account email'>
		<br/><br/>

		<a href='index.php'>
			<input type='button' value='Back' class='btn btn-primary' alt='Back button' />
		</a>

		<input type='submit' value='Continue' class='btn btn-success' alt='Register button' name='continueStepOne' />

		<br /><br />
	</form>

	<?php } elseif ( $pageVars['forgotPassStep'] == 2 ) { ?>
	<p>Step 2: Confirm email</p>
	<br />
	<p>To confirm that you are who you're saying you are, we have sent you an email containing a recover link. To change your password, all you need to do is click the link and the system will ask you to change your password. You will have 2 days to do this before your recovery token expires and you will need to click 'Forgot your password' again. If you run into any problems, email <a href='mailto:braydon.davis@mtchs.org'>Braydon Davis</a> asking for help.</p>
	<br />

	<a href='index.php'>
		<input type='button' value='Home' class='btn btn-primary' onclick='window.close();' />
	</a>

	<?php } elseif ( $pageVars['forgotPassStep'] == 3 ) { ?>
	<p>Step 3: Reset password</p>
	<br />

	<p>
		This page will allow you to change your account password! Please change it to something you'll remember so we don't have to do this again.
		<br /><br />
		<strong>Requirements</strong>
		<br/>
		- Passwords match<br/>
		- Password is at least 6 characters<br/>
		<br />
	</p>

	<?php
		if ( isset ( $messages, $messages[1] ) && count ( $messages[1] ) > 0 ){
			foreach ( $messages[1] as $i=>$msg )
				echo $msg . " <br />";

			echo "<br />";
		}
	?>

	<form action='index.php?p=forgotPassword&a=<?php echo $pageVars['forgotPassID']; ?>&key=<?php echo $pageVars['forgotPassKey']; ?>' method='POST'>
		<label>New password:</label> <input type='password' value="" name="forgotPassNewPass" placeholder='New account password' class='form-control forgotInput' required alt='New account password'>
		<br/><br/>

		<label>New password:</label> <input type='password' value="" name="forgotPassNewPassConf" placeholder='Confirm new account password' class='form-control forgotInput' required alt='Confirm new account password'>
		<br /><br />

		<a href='index.php'>
			<input type='button' value='Home' class='btn btn-primary' />
		</a>
		<input type='submit' value='Update Password' class='btn btn-success' name='btnUpdatePassword' />
	</form>

	<?php } elseif ( $pageVars['forgotPassStep'] == 4 ) { ?>
	<p>Step 4: Password updated</p>
	<br />

	<p>
		Your new password has been set! You can now <a href='index.php'>login</a> using your new credentials!
	</p>

	<?php } ?>
</div>
