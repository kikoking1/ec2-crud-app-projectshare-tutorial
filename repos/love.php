<?php

class Love extends Db {

    function add($post_id, $api_resp_data){

        $conn = $this->con();

        $post_id = mysqli_real_escape_string($conn, $post_id);
        $user_id = (int)$_SESSION['user_id'];

        // Check if already loved
        $sql = "SELECT id FROM loves WHERE post_id = '$post_id' AND user_id = '$user_id' LIMIT 1";
        $love = $this->select($sql, 1);
        
        if ( !empty($love) ) { // it's already been loved. Unlove it!!

            $sql = "DELETE FROM loves WHERE post_id = '$post_id' AND user_id = '$user_id'";
            $this->execute($sql);

            $api_resp_data['loved_state'] = 'unloved';

        } else { // It has NOT loved, Love it!!!

            // Create Love entry
            $sql = "INSERT INTO loves (post_id, user_id) VALUES('$post_id', '$user_id')";
            $this->execute($sql);

            $api_resp_data['loved_state'] = 'loved';

        } 

        // Get loves count for this post
        $sql = "SELECT COUNT(id) AS love_count FROM loves WHERE post_id = '$post_id'";
        $loves = $this->select($sql, 1);

        $api_resp_data['love_count'] = $loves['love_count'];
        $api_resp_data['error'] = false;

        return $api_resp_data;

    }

}