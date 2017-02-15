<?php

// Check if the user is trying to cheat
if ( !defined ( "FROM_INDEX" ) || !isset ( $Account ) || $Account->getRank() == 0 )
    die ( "Stop trying to hack the system!" );


// Declare what page to view
$viewPage = "all_users";

// Check to see if the user wants to view a different page
if ( isset ( $_GET['action'] ) ){
    switch ( strtolower ( $_GET['action'] ) ) {

        // View all the users
        case "viewall":
            $viewPage = "all_users";
            break;

        // Edit a user
        case "edit":
            if ( isset ( $_GET['uid'] ) ){
                $viewPage = "edit_user";
                break;
            }

        // View a specific user
        case "view":
            if ( isset ( $_GET['uid'] ) ) {
                $viewPage = "view_user";
                break;
            }

        // None - view all users
        default:
            $viewPage = "all_users";
    }
}?>
	<script type="text/javascript">
		// View a popup for a specific class
		var viewingUserId;

		function viewSpecificClass ( className ){
			var myWindow = window.open("index.php?p=adminViewUserClass&uid="+ viewingUserId +"&class=" + className, "viewClassWindow" );
		}
	</script>

    <div id='viewUsersWrapper' class='container'>
<?php
// VIEW ALL USERS PAGE
if ( $viewPage == "all_users" ) { ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><a href='index.php?p=adminViewUsers&sort=id'>ID</a></th>
                    <th><a href='index.php?p=adminViewUsers&sort=name'>Name</a></th>
                    <th><a href='index.php?p=adminViewUsers&sort=email'>Email</a></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
<?php
    // How do they want it sorted (id default)
    $sort = 'uid';

    // Valid sort methods. $sorts[$_GET['sort']]
    $sorts = array(
        "id"=>"uid",
        "name"=>"name",
        "email"=>"email"
    );

    if ( isset ( $_GET['sort'] ) ){
        $_sort = strtolower ( $_GET['sort'] );
        if ( isset ( $sorts [ $_sort ] ) )
            $sort = $sorts [ $_sort ];
    }

    // Query all users
    $_query = $Database->query ( "SELECT * FROM `users` ORDER BY `{$sort}`;" );

    // Create HTML table for all users
    while ( $row = $_query->fetch_array() ) {

?>
                <tr <?php if($row['active']==0) echo 'class="danger"'; ?> >
                    <td class='colUsrId'><?php echo $row['uid']; ?></td>
                    <td class='colUsrName'><?php echo $row['name']; ?></td>
                    <td class='colUserEmail'><?php echo $row['email']; ?></td>
                    <td class='colActions'>
                        <a class='btn btn-primary' href='index.php?p=adminViewUsers&action=view&uid=<?php echo $row['uid']; ?>'>View</a>
                        <a class='btn btn-primary' href='index.php?p=adminViewUsers&action=edit&uid=<?php echo $row['uid']; ?>'>Edit</a>
                    </td>
                </tr>
<?php
    }
?>
        </tbody>
    </table>


<?php
// VIEW SPECIFIC USER PAGE
} elseif ( $viewPage == "view_user" ) {

    // Validate user ID
    $uid = 0;
    if ( isset ( $_GET['uid'] ) ){
        $uid = intval ( $Database->escape ( $_GET['uid'] ) );
    }

    // Query the user
    $query = $Database->query ( "SELECT
        uid,
        email,
        password,
        classes,
        name,
        urank,
        active,
        activation_key,
        DATE_FORMAT(registered_at,'%d %M, %Y at %l:%i %p') AS 'registered_at',
        DATE_FORMAT(last_login,'%d %M, %Y at %l:%i %p') AS 'last_login'
     FROM users WHERE uid=$uid LIMIT 1;" );

     // Make sure the users exists
    if ( $query->num_rows == 1 ) {

        // Pull informaiton about the user & display their information
        $user = $query->fetch_array();
        ?>

		<script>
            viewingUserId = <?php echo $uid; ?>
        </script>

        <div id="adminViewUsersSpecific">
            <p>Viewing information for <strong><?php echo $user['name']; ?></strong>:</p>
            <br />
            <div class='userInfRow'>
                <span class='userCad'>Account ID:</span><span class='userVal'><?php echo $user['uid']; ?></span>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Name:</span><span class='userVal'><?php echo $user['name']; ?></span>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Email:</span><span class='userVal'><a href='mailto:<?php echo $user['email']; ?>'><?php echo $user['email']; ?></a></span>
            </div>

			<div class='userInfRow'>
                <span class='userCad'>Classes:</span>
                <div id='userClassesBlock'>
                    <div id='classList'>

                        <?php
                            $__classes = json_decode ( $user['classes'] );
                            if ( empty ( $__classes ) )
                                echo "<em>None</em>";
                            else {
                                foreach ( $__classes as $index=>$value ) {
                                    $_value = str_replace ( " ", "", $value );
                                    echo "<span id='class$_value'><a href='javascript:viewSpecificClass(\"$value\");'>$value</a><br /></span>";
                                }
                            }
                        ?>

                    </div>
                </div>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Permissions:</span><span class='userVal'><?php echo ( $user['urank'] == 0 ? "User" : "Admin" ); ?></span>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Active:</span><span class='userVal'><?php echo ( $user['active'] == 0 ? "No" : "Yes" ); ?></span>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Activation key:</span><span class='userVal'><?php echo ( $user['activation_key'] == "" ? "<em>None</em>" : $user['activation_key']  ); ?></span>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Registered:</span><span class='userVal'><?php echo $user['registered_at']; ?></span>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Last login:</span><span class='userVal'><?php echo $user['last_login']; ?></span>
            </div>

            <br />
            <a href='index.php?p=adminViewUsers' class='btn btn-primary'>Back</a>
            <a href='index.php?p=adminViewUsers&action=edit&uid=<?php echo $user['uid']; ?>' class='btn btn-secondary'>Edit</a>
        </div>
        <?php

    } else {
        echo "<br/><br/><p style='padding:20px;font-size:25px;'>Sorry, this user couldn't be found.<br/><a class='btn btn-primary' href='index.php?p=adminViewUsers'>Back</a></p><br/><br/>";
    }

// EDIT A USER
} elseif (  $viewPage == "edit_user"  ){
    // Validate user ID
    $uid = 0;
    if ( isset ( $_GET['uid'] ) ){
        $uid = intval ( $Database->escape ( $_GET['uid'] ) );
    }

    // Check if any information needs to be updated from a previous update
    if ( isset ( $_POST['hasPageBeenSubmitted'] ) ){
        // Update user account - page has been submitted;
        $_query = $Database->query("SELECT * FROM users WHERE uid=$uid");
        $user = $_query->fetch_array();

        // Set new information to old information
        $updatedName =          $user['name'];
        $updatedEmail =         $user['email'];
        $updatedClasses =       implode ( " ||| ", json_decode ( $user['classes'] ) );
        $updatedPermissions =   $user['urank'];
        $updatedActive =        $user['active'];
        $updatedActivationKey = $user['activation_key'];

        // Actually set valid new information as new information
        if ( isset ( $_POST['updateUsrName'] ) )        $updatedName = $_POST['updateUsrName'];
        if ( isset ( $_POST['updateUsrEmail'] ) )       $updatedEmail = $_POST['updateUsrEmail'];
        if ( isset ( $_POST['updateUsrClasses'] ) )     $updatedClasses = $_POST['updateUsrClasses'];
        if ( isset ( $_POST['updateUsrRank'] ) )        $updatedPermissions = intval ( $_POST['updateUsrRank'] );
        if ( isset ( $_POST['updateUsrActive'] ) )      $updatedActive = intval ( $_POST['updateUsrActive'] );
        if ( isset ( $_POST['updateUsrActivationKey'] ))$updatedActivationKey = $_POST['updateUsrActivationKey'];

        $updatedClasses = explode ( " ||| ", $updatedClasses );

        // Validate new class names
        $allValidClassNames = true;
        foreach ( $updatedClasses as $index=>$class ) {
            if ( !preg_match("/^[a-zA-Z0-9\s()-]+$/", $class ) ){
                $allValidClassNames = false;
                break;
            }
        }

        // Convert classes array to JSON string
        if ( implode ( " ||| ", $updatedClasses ) != "" )
            $updatedClasses = json_encode (  $updatedClasses );
        else
            $updatedClasses = "[ ]";

        // Update database
        $updateQuery = "UPDATE users SET
            email='{$Database->escape ($updatedEmail)}',
            classes='{$Database->escape($updatedClasses)}',
            name='{$Database->escape($updatedName)}',
            urank=$updatedPermissions,
            active=$updatedActive,
            activation_key=" .($updatedActivationKey==""?"NULL":"\"{$Database->escape($updatedActivationKey)}\"") . "
         WHERE uid=$uid";

        // Delete old assignments
        $deleteString = "DELETE FROM assignments WHERE forUser=$uid";

        foreach ( json_decode ( $updatedClasses ) as $index=>$val )
            $deleteString .= " AND forClass != '$val'";

        // Make sure all information validates

        if ( $allValidClassNames ) {
            // Send queries
            $Database->query ( $updateQuery );
            $Database->query ( $deleteString );
            // Success label
            $messages[0][] = "<br/><span class='label label-success'>User information has been updated!</span><br/>";
        } else {
            $messages[0][] = "<br/><span class='label label-danger'>Class names should only contain A-Z, 0-9, -, ( )</span><br/>";
        }
    }


    // Get specific user - new information
    $query = $Database->query ( "SELECT
        uid,
        email,
        password,
        classes,
        name,
        urank,
        active,
        activation_key,
        DATE_FORMAT(registered_at,'%d %M, %Y at %l:%i %p') AS 'registered_at',
        DATE_FORMAT(last_login,'%d %M, %Y at %l:%i %p') AS 'last_login'
     FROM users WHERE uid=$uid LIMIT 1;" );

     // Validate user
    if ( $query->num_rows == 1 ) {
        // Get infrmation about the user
        $user = $query->fetch_array();

        // Check if we want to delete the user account
        if ( isset ( $_GET['delete'] ) && $_GET['delete'] == "1" ) {
            $Account->deleteAccount ( $user['uid'] );
            die ( "<script>window.location='index.php?p=adminViewUsers'</script>" );
        }

     ?>
        <script type="text/javascript">
			viewingUserId = <?php echo $uid; ?>;

            userClasses = [ <?php
                foreach ( json_decode ( $user['classes'] ) as $index=>$value )
                    echo  "'$value',";
            ?> ];

            // Used to remove a class from the to send list
            function removeClass ( name ) {
                if ( !confirm( "Are you sure you want to delete this class?" ) ) return;
                userClasses.splice ( userClasses.indexOf ( name ), 1 );
                $("#class"+ name).remove();
            }

            // Add a class to the to send list
            function addClass ( ) {
                var c = prompt ( "What would you like to name the class?");
                if ( !c ) return false;

                for ( i = 0; i < userClasses.length; i++ ){
                    if (userClasses [ i ].toLowerCase() == c.toLowerCase() ){
                        alert ( "This user already has this class.");
                        return false;
                    }
                }

                if ( c.match (/^[a-zA-Z0-9\s()-]+$/) ){
                    userClasses.push ( c );
                    var _c = c.trim();
                    $("#classList").append("<span id='class" + _c + "'><img src='assets/img/ico_incomplete.png' width=16 onclick='removeClass(\""+_c+"\")' alt='Remove class' /> " + c + " <br /></span>");
                } else {
                    alert ( "Class names should only contain A-Z, 0-9, -, (, )")
                }
            }

            // Set the to send value for the classes to a string
            function substitueClasses ( ) {
                $("#updateUsrClasses").val ( userClasses.join ( " ||| ") );
            }

            // equivilant to htmlspecialchars
            function escapeHtml(text) {
              var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
              };

              return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            function addslashes(string) {
                return string.replace(/\\/g, '\\\\').
                    replace(/\u0008/g, '\\b').
                    replace(/\t/g, '\\t').
                    replace(/\n/g, '\\n').
                    replace(/\f/g, '\\f').
                    replace(/\r/g, '\\r').
                    replace(/'/g, '\\\'').
                    replace(/"/g, '\\"');
            }
        </script>
        <form id="adminViewUsersEdit" method='POST' action='index.php?p=adminViewUsers&action=edit&uid=<?php echo $uid; ?>' onsubmit="substitueClasses();">
            <p>Editing information for <strong><?php echo $user['name']; ?></strong>:</p>
            <br />
            <?php
                if ( isset ( $messages[0] ) && count ( $messages[0] ) > 0 ){
                    foreach ( $messages[0] as $i=>$msg )
                        echo $msg . " <br />";

                    echo "<br />";
                }
            ?>

            <div class='userInfRow'>
                <span class='userCad'>Account ID:</span><span class='userVal'><?php echo $user['uid']; ?></span>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Name:</span><input type='text' value='<?php echo $user['name']; ?>' class='form form-control usrVal' name='updateUsrName' />
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Email:</span><input type='email' value='<?php echo $user['email']; ?>' class='form form-control usrVal' required name='updateUsrEmail' />
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Classes:</span>
                <div id='userClassesBlock'>
                    <div id='classList'>
                        <?php
                            foreach ( json_decode ( $user['classes'] ) as $index=>$value ) {
                                $_value = str_replace ( " ", "", $value );
                                echo "<span id='class$_value'><img src='assets/img/ico_incomplete.png' width=16 onclick='removeClass(\"$_value\")' alt='Remove class' /> <a href='javascript:viewSpecificClass(\"$_value\");'>$value</a><br /></span>";
                            }
                        ?>
                    </div>

                    <input type="hidden" value="" name="updateUsrClasses" id="updateUsrClasses" />
                    <input value='New class' class='btn btn-success' type='button' onclick='addClass();' />
                </div>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Permissions:</span>
                <select class='form form-control usrVal' required name='updateUsrRank' size=1>
                    <option value=''>Select...</option>
                    <option value='0'<?php if($user['urank']==0) echo " selected"; ?>>User</option>
                    <option value='1'<?php if($user['urank']==1) echo " selected"; ?>>Admin</option>
                </select>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Active:</span>
                <select class='form form-control usrVal' required name='updateUsrActive' size=1>
					          <option value=''>Select...</option>
                    <option value='0'<?php if($user['active']==0) echo " selected"; ?>>No</option>
                    <option value='1'<?php if($user['active']==1) echo " selected"; ?>>Yes</option>
                </select>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Activation key:</span><input type='text' value='<?php echo $user['activation_key']; ?>' class='form form-control usrVal' name='updateUsrActivationKey' />
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Registered:</span><span class='userVal'><?php echo $user['registered_at']; ?></span>
            </div>

            <div class='userInfRow'>
                <span class='userCad'>Last login:</span><span class='userVal'><?php echo $user['last_login']; ?></span>
            </div>

            <br />
            <input type='submit' value='Save' class='btn btn-success' name='hasPageBeenSubmitted' />
            <a href='index.php?p=adminViewUsers&action=edit&uid=<?php echo $user['uid']; ?>&delete=1' class='btn btn-danger' onclick='return confirm("Are you sure you want to delete this user?");'>Delete</a>
            <a href='index.php?p=adminViewUsers&action=view&uid=<?php echo $user['uid']; ?>' class='btn btn-secondary'>View</a>
            <a href='index.php?p=adminViewUsers' class='btn btn-secondary'>Users</a>
        </form>
    <?php } else {
        echo "<br/><br/><p style='padding:20px;font-size:25px;'>Sorry, this user couldn't be found.<br/><a class='btn btn-primary' href='index.php?p=adminViewUsers'>Back</a></p><br/><br/>";
    }
}
?>

</div>
