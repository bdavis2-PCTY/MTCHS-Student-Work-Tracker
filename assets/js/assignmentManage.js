assignments = [ ];


function requestAssignmentDelete ( id ) {
	/*if ( confirm ( "Are you sure you would like to delete this assignment?" ) ) {
		window.location = "index.php?p=manager&act=delete&id="+ id;
	}*/

	if ( !confirm ( "Are you sure you would like to delete this assignment?" ) ) return;

	if ( typeof assignments [ id ] != 'undefined' ){
		var selector = ".userAssignmentRow#assignment_" + id;

		//var forClassElement = $(selector).attr("ofclass");
		//console.log(forClassElement);


		$( selector ).remove( );


		$.ajax({
			url:"index.php",
			method: "GET",
			data:{
				p:"AJAX",
				ACTION:'deleteAssignment',
				assignmentId: id,
			}
		}).fail(function( ){
			alert ( "Error communicating with server. Manually deleting......");
			window.location = "index.php?p=manager&act=delete&id="+ id;
		});

	}
}


var lastUpdate = 0;
function updateAssignmentStatus ( id, fromItem ) {
	var hasParent = false;
	if ( typeof assignments [ id ] == "undefined"  ){
		alert ( "Invalid operation" );
		return false;
	}

	var d = new Date ( );
	if( d.getTime() - lastUpdate  <= 300 ) {
		alert ( "Please refrain yourself from spam clicking");
		return false;
	}

	if ( typeof fromItem == "object" )
		hasParent = true;


	// Assignment is incomplete - Set to complete
	if ( assignments [ id ].status == "incomplete" ){
		itemToStat = 1;
		if ( hasParent ) {
			$(fromItem).attr("src","assets/img/ico_complete.png")
			$(fromItem).attr("title","Assignment complete")
			$(fromItem).attr("alt","Complete icon")
			assignments [ id ].status = "complete";
		}

	// Assignment is complete - Set to in progress
	} else if ( assignments [ id ].status == "complete" ) {
		itemToStat = 2;
		if ( hasParent ) {
			$(fromItem).attr("src","assets/img/ico_inprogress.png")
			$(fromItem).attr("title","Assignment in progress")
			$(fromItem).attr("alt","In progress icon")
			assignments [ id ].status = "in progress";
		}

	// Assignment is in progress - Set to complete
	} else {
		itemToStat = 0;
		if ( hasParent ) {
			$(fromItem).attr("src","assets/img/ico_incomplete.png")
			$(fromItem).attr("title","Assignment incomplete")
			$(fromItem).attr("alt","Incomplete icon")
			assignments [ id ].status = "incomplete";
		}
	}



	$.ajax({
		url:"index.php",
		method: "GET",
		data:{
			p:"AJAX",
			ACTION:'updateAssignmentStatus',
			assignmentId: id,
			toStat: itemToStat
		}
	}).fail(function( ){
		alert ( "Error communicating with server. Refreshing page...");
		window.location = window.location;
	}).success(refreshIncompleteAssignmentList);

	lastUpdate = d.getTime();
}

// Refresh the Incomplete Assignments side bar
function refreshIncompleteAssignmentList ( ) {
	$.ajax({
		url:"index.php",
		method: "GET",
		data:{
			p:"AJAX",
			ACTION:'sideBarNavItems'
		}
	}).fail(function( ){
		window.location = window.location;
	}).success ( function( d ){
		$("#userSideNavigationView").html ( d );
	});
}
