<?php require_once '../../../core/init.php';

$api_resp_data = array(
    'error' => true,
    'posts' => array()
);

$p = new Post;
$api_resp_data = $p->get_feed($api_resp_data);


echo json_encode($api_resp_data);

exit();