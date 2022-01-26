<?php require_once '../../../core/init.php';

$api_resp_data = array(
    'comment_count' => 0,
    'error' => true
);

if ( !empty($_POST['comment_id']) && !empty($_POST['post_id']) ) {

    $c = new Comment;
    $api_resp_data = $c->delete($_POST['comment_id'], $_POST['post_id'], $api_resp_data);

}

echo json_encode($api_resp_data);

exit();