<?php

    if ( !defined ( "FROM_INDEX" ) || !isset ( $Account ) || $Account->getRank() == 0 )
        die ( "Stop trying to hack the system!" );


    function correctResult ( $v ) {

        if ( gettype ( $v ) == "array" || gettype ( $v ) == "object" ){
            $toPrint = print_r ( $v, true );
            if ( gettype ( $toPrint ) == "string" )
                return preg_replace( "/\r|\n/", "", addslashes ( $toPrint ) );
            else
                return "UNKNOWN";
        }

        return preg_replace( "/\r|\n/", "", addslashes ( $v ) );
    }

?>
<script type="text/javascript">
    // Get server information
    sections = { }
    sections["MySQL Stats"] = { }
    sections["Error Log"] = { }
    sections["$_SERVER"]= { };
    sections["$_SESSION"] = { };
    sections["$_GET"] = { };
    sections["$_POST"] = { };
    sections["$_COOKIE"] = { };
    sections["$_REQUEST"] = { };
    sections["$_ENV"] = { };

    <?php
        $output = "";
        foreach ($_SERVER as $k=>$v )       $output .= "sections['\$_SERVER']['$k']     = '".correctResult($v)."';\n";
        foreach ($_SESSION as $k=>$v )      $output .= "sections['\$_SESSION']['$k']    = '".correctResult($v)."';\n";
        foreach ($_GET as $k=>$v )          $output .= "sections['\$_GET']['$k']        = '".correctResult($v)."';\n";
        foreach ($_POST as $k=>$v )         $output .= "sections['\$_POST']['$k']       = '".correctResult($v)."';\n";
        foreach ($_COOKIE as $k=>$v )       $output .= "sections['\$_COOKIE']['$k']     = '".correctResult($v)."';\n";
        foreach ($_REQUEST as $k=>$v )      $output .= "sections['\$_REQUEST']['$k']    = '".correctResult($v)."';\n";
        foreach ($_ENV as $k=>$v )          $output .= "sections['\$_ENV']['$k']        = '".correctResult($v)."';\n";

        // Get mySQL Stats info
        $query = $Database->query('SELECT NOW() as "now", CURDATE() as "curdate",CURTIME() as "curtime" FROM dual;');

        foreach ( $query->fetch_array() as $k=>$v){
            if ( gettype ( $k ) == "string" )
                $output .= "sections['MySQL Stats']['$k'] = '".correctResult($v)."';\n";
        }

        $output .= "sections['MySQL Stats']['mysql_server_info'] = '". correctResult($Database->server_info) ."';\n";
        $output .= "sections['Error Log']['log'] = '".correctResult(file_get_contents("error_log"))."'";

        echo $output;
    ?>


    // Hide/Show buttons
    var values = [ ];
    $(document).ready(function(){
        // Set page contents
        var fString = "";
        for ( var i in sections ) {
            fString += "<p>"+i+"<br /><span class='statValue'>";
            for ( var i2 in sections [ i ] )
                fString += "[" + i2 +"]: " + sections [ i ] [ i2 ] + "<br/>";
            fString += "</span><br/></p><br/><Br/>";
        }
        $("#pageViewStatsWrapper").html ( fString );


		$(".statValue").each(function(i, v){
			values[i] = { value: $(this).parent().html(), status: 0 };
			$(this).html("");

            $(this).addClass("for"+i);
			$(this).parent().html($(this).parent().html() + "<input type='button' value='Show' onclick='showHideArea("+i+");' class='for"+i+"' />");
		});

	});

	function showHideArea ( i ) {
		if ( values [ i ].status == 0 ){
            values [ i ].status = 1;
            $(".statValue.for" + i ).html( values [ i ].value );
            $(".for"+i+"[type=button]").attr("value","Hide");
        } else {
            values [ i ].status = 0;
            $(".statValue.for" + i ).html( "" );
            $(".for"+i+"[type=button]").attr("value","Show");
        }
	}
</script>


<div id="pageViewStatsWrapper">

</div>
