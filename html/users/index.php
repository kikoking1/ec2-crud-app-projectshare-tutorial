<?php  require_once("../../core/init.php");
    (function(){
        // set dynamic vars to pass to html_head.php
        $title = 'Profile Page';
        $description = '';
        require_once(APP_ROOT."/elements/html_head.php");
        require_once(APP_ROOT."/elements/nav.php");
    })();

    $u = new User;
    $user = $u->get_by_id($_SESSION['user_id']);

?>

<div class="container">

    <div class="row">
        <div class="col-sm-6">

            <h2>My Profile</h2>
            <hr>

            <h4>Username</h4>
            <p><?=$user['username']?></p>

            <h4>First Name</h4>
            <p><?=$user['firstname']?></p>

            <h4>Last Name</h4>
            <p><?=$user['lastname']?></p>

            <h4>Timezone</h4>
            <p><?=$user['timezone']?></p>

            <h4>Bio</h4>
            <p><?=$user['bio']?></p>

            <a class="btn btn-primary" href="/users/edit.php">Edit Profile</a>

        </div><!-- .col-sm-6 -->
    </div><!-- .row -->

</div><!-- .container -->

<?php require_once(APP_ROOT."/elements/footer.php");
