<?php

// Check if the user is trying to cheat
if ( !defined ( "FROM_INDEX" ) || !isset ( $Account ) || $Account->getRank() == 0 )
    die ( "Stop trying to hack the system!" );

$userId = 0;
$className = "";

if ( isset ( $_GET['uid'] ) )
    $userId = intval ( $_GET['uid'] );

if ( isset ( $_GET['class'] ) )
    $className = $_GET['class'] ?>

<script type="text/javascript">
    var editingId = <?php echo $userId; ?>;
    var edingClass= "<?php echo $className; ?>";

    function openNewAssignmentWindow ( ) {
        var addWindow = window.open("index.php?p=adminAddUserAssignment&uid="+ editingId +"&class=" + edingClass, "addAssignmentWindow", "width=1000, height:400" );
        addWindow.onbeforeunload = function ( ) {
            setTimeout(function(){
                window.location.reload();
            },100);
        }
    }
</script>

<div id='adminViewUserClassWrapper'>

<?php
// Make sure it's a valid user ID
$queryUser = $Database->query ( "SELECT * FROM users WHERE uid=". $Database->escape ( $userId )." LIMIT 1;" );
if ( $queryUser->num_rows == 1){
    $user = $queryUser->fetch_array();


    //Make sure it's a valid class for the user
    $classes = array ( );
    $_classes = json_decode ( $user['classes'] );
    foreach ( $_classes as $index=>$class )
        $classes[$class] = true;


    if ( isset ( $classes[$className] ) ) {
        // Check for assignment status update
        if ( isset ( $_POST['assignmentId'], $_POST['updatedAssignmentStatus'] ) ){
            $q = $Database->query("SELECT uid FROM assignments WHERE uid={$Database->escape($_POST['assignmentId'])} AND forUser={$Database->escape($userId)} LIMIT 1;");
            if ( $q->num_rows == 1 )
                $Database->query("UPDATE assignments SET assignmentStatus=".intval($_POST['updatedAssignmentStatus'])." WHERE uid={$Database->escape($_POST['assignmentId'])} AND forUser={$Database->escape($userId)}");
        }

        // Check for delete
        if ( isset ( $_GET['a'] ) ){
            $a = $_GET['a'];
            if ( $a == "delete" ) {
                if ( isset ( $_GET['aid' ] ) )
                $Database->query("DELETE FROM assignments WHERE forUser=$userId AND uid={$Database->escape($_GET['aid'])}");
                die ( "Deleting, please wait...<script>window.location = 'index.php?p=adminViewUserClass&uid={$userId}&class={$className}';</script>" );
            }
        }

        // Get assignments for this class
        $queryAssignments = $Database->query(
            "SELECT
                uid,
                assignmentName,
                DATE_FORMAT(DATE_SUB(dueDate, INTERVAL 7 HOUR),'%d %M, %Y') AS 'dueDate',
                IF(assignmentStatus=0,'incomplete','complete') AS 'status'
            FROM assignments WHERE
                   forClass='".$Database->escape($className)."' AND forUser={$userId}"
               );

        echo $queryAssignments->error;

        while ( $assignment = $queryAssignments->fetch_array() ){
            $assignments[] = $assignment;
        }

        ?>
    <p>Viewing assignments for class <strong><?php echo $className; ?></strong> for <strong><?php echo $user['name']; ?></strong></p>
    <?php
        if ( empty ( $assignments ) )
            echo "  <p>This user has no assignments for this class.</p>";
        else { ?>

    <table class="table table-hover">
        <thead>
            <th>Name</th>
            <th>Due</th>
            <th>Status</th>
            <th>Actions</th>
        </thead>

        <tbody>
        <?php
            foreach ( $assignments as $index=>$assignment ) { ?>
            <tr>
                <td><?php echo $assignment['assignmentName']; ?></td>
                <td><?php echo $assignment['dueDate']; ?></td>
                <td>
                    <form action='index.php?p=adminViewUserClass&uid=<?php echo $userId; ?>&class=<?php echo $className; ?>' method='POST' onchange='this.submit();'>
                        <input type='hidden' value='<?php echo $assignment['uid'];?>' name='assignmentId' />
                        <select name='updatedAssignmentStatus'>
                            <option value='0'>Incomplete</option>
                            <option value='1'<?php if($assignment['status']=='complete') echo " selected"; ?>>Complete</option>
                        </select>
                    </form>
                </td>
                <td>
                    <a href='<?php echo "index.php?p=adminViewUserClass&uid=$userId&class=$className&aid={$assignment['uid']}&a=delete"; ?>' onclick='return confirm("Are you sure you want to delete this assignment?");'>Delete</a>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

<?php
        }
    } else {
        echo "This class doesn't exist for this user.";
    }
} else {
    echo "This user doesn't exist in the database.";
}
?>
    <br />
    <a href="javascript:window.close();" class='btn btn-primary'>Close Window</a>
    <a href="javascript:openNewAssignmentWindow();" class='btn btn-primary'>New assignment</a>
    <br />
</div>
