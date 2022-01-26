<?php require_once '../../../core/init.php';

if ( !empty($_POST['post_id']) ) {

    $api_resp_data = array(
        'error' => true,
        'love_count' => '',
        'loved_state' => ''
    );

    $l = new Love;

    $api_resp_data = $l->add($_POST['post_id'], $api_resp_data);

    echo json_encode($api_resp_data);

}


exit();