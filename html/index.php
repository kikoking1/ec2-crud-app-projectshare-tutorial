<?php  require_once("../core/init.php");

    if ( !empty($_SESSION['user_id']) ) { // They ARE logged in

        // They shouldn't on the login page, if they are already logged in.
        // let's redirect them.
        header("Location: /posts/");
        exit();

    }

    (function(){
        // set dynamic vars to pass to html_head.php
        $title = 'Home Page';
        $description = '';
        require_once(APP_ROOT."/elements/html_head.php");
        require_once(APP_ROOT."/elements/nav.php");
    })();

?>

<div class="container">
    
    <h1>Welcome to Project Share App!</h1>

    <?php // Display error message
    if ( !empty($_SESSION['api_msg_errs']) ) {

        echo '<p class="text-danger text-center">';

            foreach ( $_SESSION['api_msg_errs'] as $error ) {
                echo $error;
                echo '<br>';
            }

        echo '</p>';

        unset($_SESSION['api_msg_errs']);
    }
    ?>

    <div class="row">
        
        <div class="col-sm-6">

            <h2>Sign In</h2>


            <form action="/api/users/login.php" method="post">

                <div class="form-group">
                    <label>Username</label>
                    <input class="form-control" type="text" name="username" required>
                </div>  
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" type="password" name="password" required>
                </div>

                <input type="submit" value="Submit">

            </form>

        </div><!-- .col-sm-6 -->

        <div class="col-sm-6">
        
            <h2>Create New Account</h2>

            <form action="/api/users/add.php" method="post">
            
                <div class="form-group">
                    <label>Username</label>
                    <input class="form-control" type="text" name="username" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input class="form-control" type="password" name="password" autocomplete="new-password" required>
                </div>
            
                <div class="form-group">

                    <label>Your Timezone</label>
                    <select id="signup-timezone-select" class="form-control chosen-select" name="timezone" required>
                    
                        <option></option>

                        <?php
                        $timezone_identifiers = DateTimeZone::listIdentifiers();

                        foreach ($timezone_identifiers as $timezone) {
                            echo '<option>'.$timezone.'</option>';
                        }
                        ?>

                    </select>

                </div>

                <h4>Profile Info</h4>
                <hr>

                <div class="form-group">
                    <label>First Name</label>
                    <input class="form-control" type="text" name="firstname" required>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input class="form-control" type="text" name="lastname" required>
                </div>
                <div class="form-group">
                    <label>Bio</label>
                    <textarea class="form-control" name="bio" required></textarea>
                </div>

                <input type="submit" value="SUBMIT">

            </form>

        </div><!-- .col-sm-6 -->
                        
    </div><!-- .row -->

</div><!-- .container -->

<?php require_once(APP_ROOT."/elements/footer.php");
