<?php
	if ( !defined ( "FROM_INDEX" ) )
		die ( "Stop trying to hack the system!" );

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<title>MTCHS Student Work Tracker</title>
		<meta charset="UTF-8" />

		<!-- Default styles -->
		<link rel="stylesheet" type="text/css" href="assets/css/reset.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/styles.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css" />
		<link rel="stylesheet" type="text/css" href="assets/css/jquery-ui.min.css" />

		<!-- Load JavaScript  -->
		<script src="assets/js/jquery-2.1.4.min.js" type="text/javascript"></script>
		<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
		<script src="assets/js/jquery-ui.min.js" type="text/javascript"></script>

		<!-- Favicon -->
		<link rel="shortcut icon" href="favicon.ico" />

	</head>

	<body>
		<header>
			<div id="headerBar">
				<p><a href='index.php'>MTCHS Student Work Tracker</a></p>

				<?php if ( isset ( $Account ) && $Account->isUserLoggedIn() ) { ?>
				<div id='navigation'>
					<ul>
						<!--<li><a href="http://mtchs.powerschool.com" target=_blank><img src='assets/img/ico_powerschool.png' alt='Powerschool' title='Powerschool'></a></li>
						<li><a href="http://cds.mtchs.org/moodle/login/index.php" target=_blank><img src='assets/img/ico_moodle.png' alt='Moodle' title='Moodle'></a></li>-->
						<?php if ( $Account->getRank() > 0 ){ ?>
							<li><a href="index.php?p=adminViewStats"><img src='assets/img/ico_serverStats.png' alt='ADMIN: View stats' title='ADMIN: View stats'></a></li>
							<li><a href="index.php?p=adminViewUsers"><img src='assets/img/ico_view_users.png' alt='ADMIN: View users' title='ADMIN: View users'></a></li>
						<?php } ?>

						<li><a href="index.php?p=myProfile"><img src='assets/img/ico_my_profile.png' alt='My profile' title='My profile'></a></li>
						<li><a href="index.php?p=newAssignment"><img src='assets/img/ico_new_assignment.png' alt='Add new assignment' title='Add new assignment'></a></li>
						<li><a href="index.php?forceLogOut=1"><img src='assets/img/ico_logout.png' alt='Logout' title='Logout'></a></li>

					</ul>
				</div>
				<?php } ?>
			</div>
		</header>
