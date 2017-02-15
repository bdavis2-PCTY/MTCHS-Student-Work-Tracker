<?php

	if ( !defined ( "FROM_INDEX" ) || !isset ( $Account ) || !$Account->isUserLoggedIn() )
		die ( "Stop trying to hack the system!" );

?>

<div id='myProfileWrapper'>

	<h1>Your Profile</h1>

	<!-- General account -->
	<form method="POST" action="index.php?p=myProfile">
		<h2>General</h2>
		<?php
			if ( isset ( $messages[0] ) && count ( $messages[0] ) > 0 ){
				foreach ( $messages[0] as $i=>$msg )
					echo $msg . " <br />";

				echo "<br />";
			}
		?>
		<label>Your name:</label> <input type="text" value="<?php echo $_SESSION['name']; ?>" name="usrName" class="form-control" required /><br />
		<label>Email address:</label> <input type="email" value="<?php echo $Account->getEmail(); ?>" name="usrEmail" class="form-control" disabled required /><br />
		<label>Permissions:</label> <input type="text" value="<?php echo $Account->getRankAsString(); ?>" name="usrEmail" class="form-control" disabled required /><br />
		<label>Current password:</label> <input type="password" name="usrConfCurrentPassGnrl" class="form-control" required />
		<input type="submit" value="Update changes" class='btn btn-primary' name='updateGeneral' />
	</form>

	<!-- Change password -->
	<br /><br />
	<form method="post" action="index.php?p=myProfile">
		<h2>Change Password</h2>
		<?php
			if ( isset ( $messages[1] ) && count ( $messages[1] ) > 0 ){
				foreach ( $messages[1] as $i=>$msg )
					echo $msg . " <br />";

				echo "<br />";
			}
		?>
		<label>New password:</label> <input type="password" name="usrNewPassword" class="form-control" required /><br />
		<label>Confirm password:</label> <input type="password" name="usrConfNewPassword" class="form-control" required /><br />
		<label>Current password:</label> <input type="password" name="usrConfCurrentPassChng" class="form-control" required />
		<input type="submit" value="Update changes" class='btn btn-primary' name='updatePassword'  />
	</form>

	<!-- Viewing options -->
	<Br/><br/>
	<form method="post" action="index.php?p=myProfile">
		<h2>Sorting options</h2>
		<?php
			if ( isset ( $messages[2] ) && count ( $messages[2] ) > 0 ){
				foreach ( $messages[2] as $i=>$msg )
					echo $msg . " <br />";

				echo "<br />";
			}
		?>
		<label>Sort classes:</label>
		<select name='viewSortClasses' required size=1 class='form form-control' id="viewSortClassesBy">
				<option value=''>Select...</option>
				<option value='byCreation'>By creation (first-first)</option>
				<option value='byCreationDESC'>By creation (first-last)</option>
				<option value='byClassName'>Class name (A-Z)</option>
				<option value='byClassNameDESC'>Class name (Z-A)</option>
		</select>

		<label>Sort assignments:</label>
		<select name='viewSortAssignments' required size=1 class='form form-control' id="viewSortAssignmentsBy">
				<option value=''>Select...</option>
				<option value='byCreation'>By creation (created first-display first)</option>
				<option value='byCreationDESC'>By creation (created first-display last)</option>
				<option value='byAssignmentName'>Assignment name (A-Z)</option>
				<option value='byAssignmentNameDESC'>Assignment name (Z-A)</option>
				<option value='byAssignmentStatus'>Assignment status (incomplete-complete)</option>
				<option value='byDueDate'>Assignment due-date (first due-last due)</option>
				<option value='byDueDateDESC'>Assignment due-date (last due-first due)</option>
		</select>
		<br />
		<input type="submit" value="Update changes" class='btn btn-primary' name='updateViewSettings'  />
	</form>

</div>


<script type='text/javascript'>
$(document).ready(function(){
	// Sorting classes select
	var sortClassByCookie = "<?php echo $_COOKIE['sortClasses']; ?>";
	if ( sortClassByCookie )
		$("#viewSortClassesBy").val ( sortClassByCookie );

	// Sorting assignments select
	var sortAssignmentByCookie = "<?php echo $_COOKIE['sortAssignments']; ?>";
	if ( sortAssignmentByCookie )
		$("#viewSortAssignmentsBy").val ( sortAssignmentByCookie );

});
</script>
