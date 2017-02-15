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

<div id='adminAddUserAssignmentWrapper'>

<?php
if ( !defined ( "FROM_INDEX" ) || !isset ( $Account ) || $Account->getRank() == 0 )
    die ( "Stop trying to hack the system!" );

if ( !isset ( $_GET['class'], $_GET['uid'] ) )
    die ( "Error!<script>window.close();</script>" );

$className = $_GET['class'];
$uid = intval ( $_GET['uid'] );

// Check if user exists
$query = $Database->query("SELECT name, classes FROM users WHERE uid=". $Database->escape ( $uid )." LIMIT 1;" );
if ( $query->num_rows == 1 ) {
    $queryResult = $query->fetch_array();

    $_classes = json_decode($queryResult['classes']);
    $classes = array ( );

    foreach ( $_classes as $ind=>$class )
        $classes[$class] = true;

    if ( isset ( $classes[$className ] ) ) {

        echo "<p>Adding assignment for user <strong>". $queryResult['name'] ."</strong> class <strong>". $className. "</strong>.</p>";

        if ( isset ( $_POST['newAssignmentName'], $_POST['newAssignmentDueDate'] ) ){
            $assignmentName =  trim ( $_POST['newAssignmentName'] );
            $assignmentDue =  trim ( $_POST['newAssignmentDueDate'] );

            $dueNums = explode ( "-", $assignmentDue );

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

                        $query2 = $Database->query ( "SELECT uid FROM assignments WHERE LOWER(assignmentName)=LOWER('".$Database->escape($assignmentName)."') AND forUser=". $Database->escape($uid). " LIMIT 1;");
                        if ( $query2->num_rows == 0 ) {
                            $_assignmentName = $Database->escape ( $assignmentName );
                            $_dueDate = $Database->escape ( $assignmentDue );
                            $_assignmentStatus = 0;
                            $_forClass = $Database->escape ( $className );
                            $_forUser = $uid;

                            $Database->query("INSERT INTO assignments (
                                assignmentName,
                                dueDate,
                                assignmentStatus,
                                forClass,
                                forUser )
                            VALUES (
                                '{$_assignmentName}',
                                '{$_dueDate}',
                                {$_assignmentStatus},
                                '{$_forClass}',
                                {$_forUser} );");

                            echo "<span class='label label-success'>Assignment has been added!</span><br /><script>window.close();</script>";
                        }else echo "<span class='label label-danger'>This user already has this assignment</span><br />";
                    } else echo "<span class='label label-danger'>Invalid date, please try again</span><br />";
                } else echo "<span class='label label-danger'>Invalid date, please try again</span><br />";
            } else echo "<span class='label label-danger'>Invalid date, please try again</span><br />";
        }

        ?>
        <br />
        <form action='index.php?p=adminAddUserAssignment&uid=<?php echo $uid; ?>&class=<?php echo $className; ?>' method='POST' onsubmit='return testAddAssignment();'>
            <label>Name:</label> <input type='text' value='' placeholder='Assignment name' name='newAssignmentName'class='form-control' required>
            <br />
            <label>Due date:</label> <input type='text' value='' placeholder='Assignment due date (YYYY-MM-DD)' name='newAssignmentDueDate' class='form-control datepicker' required>
            <br />
            <input type='submit' value='Add' class='btn btn-success' />
            <a href='javascript:window.close();' class='btn btn-secondary'>Close</a>
        </form>
        <br />
<?php
    } else {
        echo "<p>This user no longer has this class.<br/><a href='javascript:window.close();' class='btn btn-warning'>Close</a></p>";
    }
} else {
    echo "<p>Something went wrong, this user cannot be found!<br/><a href='javascript:window.close();' class='btn btn-warning'>Close</a></p>";
}

?>

<form action='index.php?p=adminAddUserAssignment&uid=1&class=Government' method='POST'>

</form>

</div>
