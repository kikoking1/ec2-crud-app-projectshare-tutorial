<?php require_once '../../../core/init.php';

if ( !empty( $_GET ) && !empty( $_GET['id'] ) ) {

    $p = new Post;
    $p->delete($_GET['id']);

}

header("Location: /posts/");
exit();