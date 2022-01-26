<?php require_once '../../../core/init.php';

// If $_FILES is completely empty, maybe exceeded upload_max_filesize and/or post_max_size in php.ini
if ( empty($_FILES) ) {

    $_SESSION['api_msg_errs'][] = '* An unknown error occured. Check your filesize does not exceed 5MB.';

// Make sure form data is submitted.
} else if ( !empty($_POST) && !empty($_POST['title']) && !empty($_POST['description']) ) {

    // Add a new post to the database.
    $p = new Post;
    $p->add($_POST);

} else {
    $_SESSION['api_msg_errs'][] = '* All fields required';
}

header("Location: /posts/");
exit();