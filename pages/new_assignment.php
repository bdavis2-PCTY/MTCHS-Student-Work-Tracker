<?php

	if ( !defined ( "FROM_INDEX" ) || !isset ( $Account ) || !$Account->isUserLoggedIn() )
		die ( "Stop trying to hack the system!" );


	/* ASSIGNMENT STATUSES:

	0: Incomplete, not started
	1: Started, incompolete
	2: Compolete

	*/

	global $Account;

?>

<script type="text/javascript">
$(document).ready(function(){
	$('.datepicker').datepicker({
		dateFormat: "yy-mm-dd"
	});
});

function isValidDate(dateString) {
	var regEx = /^\d{4}-\d{2}-\d{2}$/;
	return dateString.match(regEx) != null;
}

function testAddAssignment( ) {
	var valid;
	var i = $(".datepicker").val();

	valid = isValidDate ( i )

	if ( valid ) {
		var exploded = i.split("-");

		// Validate year
		if ( parseInt ( exploded [ 0 ] ) <= 1900 )
			valid = false;

		// Validate month
		if ( parseInt ( exploded [ 1 ] ) < 1 || parseInt ( exploded [ 1 ] ) > 12 )
			valid = false;

		// Validate day
		if ( parseInt ( exploded [ 2 ] ) < 1  || parseInt ( exploded [ 1 ] ) > 31 )
			valid = false;

	}

	if ( !valid )
		alert ( "Please use the specified date format of YYYY-MM-DD (Eg. 2016-06-18)")

	return valid;
}
</script>

<div id='myProfileWrapper'>

	<h1>New Assignments & Class Management</h1>
	<!-- New Assignment Area -->
	<form method="POST" action="index.php?p=newAssignment#newAssignment" id="newAssignment" onsubmit="return testAddAssignment();">
		<h2>New Assignment</h2>
		<?php
			if ( isset ( $messages[0] ) && count ( $messages[0] ) > 0 ){
				foreach ( $messages[0] as $i=>$msg )
					echo $msg . " <br />";

				echo "<br />";
			}
		?>
		<label>Assignment name:</label> <input type="text" value="" name="newAssignmentName" class="form-control" required maxlength="150" autocomplete="off" /><br />
		<label>Assignment due date:</label> <input type="text" value="" name="newAssignmentDue" class="form-control datepicker" required style="width:370px;display:inline" autocomplete="off" placeholder="YYYY-MM-DD (Eg. 2016-06-21)"/><br />
		<label>For class:</label>

		<select name="newAssignmentClass" class="form-control" required style="width:370px;display:inline" size=1>
			<option value=''>Select...</option>
			<?php
				global $Account;
				foreach ( $Account->getClasses() as $index=>$assignments )
					echo "<option value='{$index}'".(isset($_GET["class"])&&$_GET['class']==$index?" selected":"").">{$index}</option>";

			?>
		</select>
		<br/>
		<input type="submit" value="Add assignment" class='btn btn-primary' name='addNewAssignment' />
	</form>

	<!-- New Class Area -->
	<br/><br/>
	<form method="POST" action="index.php?p=newAssignment#newClass" id="newClass">
		<h2>New Class</h2>
		<?php
			if ( isset ( $messages[1] ) && count ( $messages[1] ) > 0 ){
				foreach ( $messages[1] as $i=>$msg )
					echo $msg . " <br />";

				echo "<br />";
			}
		?>
		<label>Class name:</label> <input type="text" value="" name="newClassName" class="form-control" required maxlength="50" autocomplete="off" /><br />
		<input type="submit" value="Add class" class='btn btn-primary' name='addNewClass' />
	</form>


	<!-- Remove class area -->
	<br/><br/>
	<form method="POST" action="index.php?p=newAssignment#removeClass" id="removeClass">
		<h2>Remove Class</h2>
		<?php
			if ( isset ( $messages[2] ) && count ( $messages[2] ) > 0 ){
				foreach ( $messages[2] as $i=>$msg )
					echo $msg . " <br />";

				echo "<br />";
			}
		?>

		<label>Remove class:</label>
		<select name="removeClassName" class="form-control" required style="width:370px;display:inline" size=1>
			<option value=''>Select...</option>
			<?php
				global $Account;
				foreach ( $Account->getClasses() as $index=>$assignments )
					echo "<option value='{$index}'>{$index}</option>";
			?>
		</select>
		<br />
		<input type="submit" value="Remove class" class='btn btn-primary' name='removeClass' />
	</form>
</div>
