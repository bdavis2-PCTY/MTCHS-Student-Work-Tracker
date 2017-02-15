<div id='forgotPasswordForm'>
    <h1>Enter your email address</h1>
    <br />

    <?php

        if ( isset ( $_POST['activationEmailBtn'], $_POST['activationEmail'] ) ) {
            $email = $Database->escape ( $_POST['activationEmail'] );

            $query = $Database->query ( "SELECT activation_key FROM users WHERE email='$email' AND active=0 LIMIT 1;" );
            if ( $query->num_rows > 0 ) {
                if($Account->sendAccountConfirmation ( $email ))
                    echo "<span class='label label-success' style='font-size:15px;'>An activation message has been sent to this email address <a href='index.php'>Login</a></span><br /><br />";
                else
                    echo "<span class='label label-danger' style='font-size:15px;'>Something went wrong and the email could not be sent at this time</span><br /><br />";
            } else {
                echo "<span class='label label-warning' style='font-size:15px;'>This acccount is either already active or doesn't exist</span><br /><br />";
            }
        }

    ?>

    <form action="index.php?p=resendConf" method="POST">
        <input type="email" class="form-control" name="activationEmail" placeholder="Email" style="width:450px;display:inline;" required />
        <input type="submit" value="Send email" class="btn btn-success" name="activationEmailBtn"  />
    </form>
    <br /><br />
</div>
