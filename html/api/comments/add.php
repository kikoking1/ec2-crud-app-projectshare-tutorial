<?php require_once '../../../core/init.php';

$api_resp_data = array(
    'error' => true,
    'comments' => array(),
    'comment_count' => 0,
);

if ( !empty($_POST['post_id']) ) {

    $c = new Comment;
    $api_resp_data = $c->add($_POST['post_id'], $_POST['comment'], $api_resp_data);

}

echo json_encode($api_resp_data);

exit();