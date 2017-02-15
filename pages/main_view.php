<?php
	if ( !defined ( "FROM_INDEX" ) || !isset($Account) || !$Account->isUserLoggedIn() )
		die ( "Stop trying to hack the system!" );

?>

<script src="assets/js/assignmentManage.js" type="text/javascript"></script>

<div id="userSideNavigationView">

	<p>Incomplete assignments</p>

	<?php

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

			echo "<ul>";
				echo "<li class='sideNavClass'>{$class}";
					echo "<ul>";
					foreach ( $assignments as $assignment=>$info ){
						if ( $info['status'] != "complete" ){
							$remaining = "remaining";


							echo "<li class='sidNavAssignment'>{$info['title']}<br/>
							<em>".($info['remaining']<0?$info['remaining']*-1:$info['remaining'])." day".(($info['remaining']==1||$info['remaining']==-1)?"":"s")." ".($info['remaining']<0?"late":"remaining")."</em></li>";
						}
					}
					echo "</ul>";
				echo "</li>";
			echo "</ul>";
		}

	?>

</div>


<div id='usersMainContentArea'>
	<p>Hello there, <strong><?php echo $Account->getName(); ?></strong>!<br/>
	<em style="font-size:17px;">MTCHS Student Work Tracker is still being developed. Please report any glitches or errors to <a href='mailto:braydon.davis@mtchs.org'>Braydon Davis</a>.</em></p>

	<table id='userMainClassList'>

		<?php
			if ( count ( $classes ) > 0 ){

				$JavaScriptExecute="";
				foreach ( $classes as $class=>$assignments ) {
					echo "<tr class='userClassRow' id='class_{$class}'><td colspan=4 class='userMainClass'>{$class}</td></tr>";

					if ( count ( $assignments ) > 0 ) {
						foreach ( $assignments as $index=>$info ) {

							$assignmentClasses = "userMainAssignmentInfo";
							if ( $info['remaining'] < 0 && $info['status'] != "complete" ) {
								$assignmentClasses = $assignmentClasses. " late_incomplete";

							} elseif ( $info['remaining'] == 0 && $info['status'] != "complete" ) {
								$assignmentClasses = $assignmentClasses. " due_today_incomplete";

							} elseif ( $info['status'] != "complete" ) {
								$assignmentClasses = $assignmentClasses. " notlate_incomplete";
							}

							echo "<tr class='userAssignmentRow' id='assignment_{$info['uid']}' ofclass='{$class}'>
								<td class='$assignmentClasses column_assignmentName'>{$info['title']}</td>
								<td class='$assignmentClasses column_dueDate'>{$info['due_date']}</td>
								<td class='$assignmentClasses column_assignmentStatus'><img src='assets/img/ico_".str_replace(" ","",$info['status']).".png' alt='{$info['status']} Icon' title='Assignment {$info['status']}' onclick='updateAssignmentStatus({$info['uid']}, this)' /></td>
								<td class='$assignmentClasses column_actions'><img src='assets/img/ico_delete.png' alt='Delete Assignment' onclick='requestAssignmentDelete({$info['uid']})' class='pointerOnHover'></td>
							</tr>";

							$JavaScriptExecute .= 'assignments['.$info['uid'].'] = {name:"'.$info['title'].'",status:"'.$info['status'].'",due: "'.$info['due_date'].'",remaining:'.$info['remaining'].'};
							';
						}
					} else {
						echo "<tr class='userAssignmentRow'>
							<td colspan=4>
								<em style='margin-left:30px;font-style:italic'>There are no assignments for this class, <a href='index.php?p=newAssignment&class=".urlencode($class)."' style='font-weight:bold;'>add one</a>!</em>
							</td>
						</tr>";
					}
				}

				echo "<script>{$JavaScriptExecute}</script>";

			} else {
				echo "<br/><br/><p>It seems to be that you don't have any classes or assignments. Would you like to <a href='index.php?p=newAssignment' style='font-weight:bold;'>add one</a>?</p>";
			}
		?>
	</table>
</div>
