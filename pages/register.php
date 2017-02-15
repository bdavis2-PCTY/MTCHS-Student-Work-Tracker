<?php
if ( !defined ( "FROM_INDEX" ) )
	die ( "Stop trying to hack the system!" );

$step = 1;

if ( isset ( $pageVars['registerPageStep'] ) )
	$step = $pageVars['registerPageStep'];

if ( $step == 1 ) {
?>
<div id='registerForm'>

	<h1>Registration</h1>
	<p>Register your account for the MTCHS Student Work Tracker. In order to register an account, you must have an @mtchs.org email address. Once you submit your account, you will have to verify your email address. You'll have 24 hours to verify your account before it expires and you'll need to register again.<br/><br/>
	<strong>Requirements</strong>
	<br/>
	 - Email is valid<br/>
	 - Emails match<br/>
	 - Email is @mtchs.org<br/>
	 - Passwords match<br/>
	 - Password is at least 6 characters<br/>
	 - Accept Terms of Service</p>
	<br/>

	<?php
		if ( isset ( $messages, $messages[0] ) && count ( $messages[0] ) > 0 ){
			foreach ( $messages[0] as $i=>$msg )
				echo $msg . " <br />";

			echo "<br />";
		}
	?>

	<form action='index.php?p=register' method='post'>
		<label>Email:</label> <input type='email' value="<?php echo (isset($_POST['register_email']) ? $_POST['register_email'] : "" ) ?>" name="register_email" placeholder='Your @mtchs.org email address' class='form-control registerInput' required>
		<br/><br/>
		<label>Confirm Email:</label> <input type='email' name="register_email_conf" placeholder='Confirm our @mtchs.org email address' class='form-control registerInput' required>
		<br /><br />
		<label>Password:</label> <input type='password' name="register_password" placeholder='Your account password' class='form-control registerInput' required>
		<br /><br />
		<label>Confirm Password:</label> <input type='password' name="register_password_conf" placeholder='Confirm your account password' class='form-control registerInput' required>
		<br /><br />
		<label>Grade:</label>
		<select name='register_gradeLevel' required class='form-control registerInput' size=1>
			<option value='0' selected>Freshman</option>
			<option value='1'>Sophomore</option>
			<option value='2'>Junior</option>
			<option value='3'>Senior</option>
		</select>
		<br/><br/>
		<p><input type="checkbox" required value="" /> I have read and accept to the <a href='terms_of_service.php' target=_blank>Terms of Service</a>.</p>
		<br /><br />
		
		<a href='index.php' class='btn btn-primary'>Back</a>

		<input type='submit' value='Register' class='btn btn-success' name='registerForm' />

		<br /><br />
	</form>
</div>


<?php } elseif ( $step == 2 ) { ?>
<div id='registerForm'>
	<h1>Registration</h1>
	<br/>
	<p>Your account has been registered however it is not active. To activate your account, you need to verify it. To verify your account, we have sent you an email containing a confirmation link. All you have to do is open this email and click this link. You will have 48 hours to confirm your account before it is deleted. <br/><Br/>If you are having problems finding the email, <strong>check your spam folder!</strong></p>
	<br />
	<a href='index.php'><input type='button' value='Home' class='btn btn-primary' /></a>
	<br />
</div>
<?php } ?>
