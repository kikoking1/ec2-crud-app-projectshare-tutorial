<?php

class Comment extends Db {

    function add($post_id, $comment, $api_resp_data) {

        $conn = $this->con();

        $post_id = (int)$post_id;
        $user_id = (int)$_SESSION['user_id'];
        $comment = mysqli_real_escape_string($conn, $comment);
        $posted_time = time();

        // Insert new comment        
        $sql = "INSERT INTO comments (comment, post_id, user_id, posted_time)
                VALUES ('$comment', '$post_id', '$user_id', '$posted_time')";

        $this->execute($sql);

        // Get all project comments
        $sql = "SELECT comments.id, comments.comment, comments.posted_time, users.username, users.firstname, users.lastname, users.timezone,
        IF(comments.user_id = '$user_id', 'true', 'false') AS user_owns
        FROM comments JOIN users ON comments.user_id = users.id
        WHERE comments.post_id = '$post_id'
        ORDER BY posted_time DESC
        ";

        $api_resp_data['comments'] = $this->select($sql);

        $sql = "SELECT COUNT(id) AS comment_count FROM comments WHERE post_id = '$post_id'";

        $comment_count_arr = $this->select($sql, 1);

        $api_resp_data['comment_count'] = $comment_count_arr['comment_count'];

        $api_resp_data['error'] = false;

        return $api_resp_data;

    }

    function delete($comment_id, $post_id, $api_resp_data) {

        $conn = $this->con();

        $comment_id = (int)$comment_id;
        $post_id = (int)$post_id;
        $user_id = (int)$_SESSION['user_id'];

        // Insert new comment        
        $sql = "DELETE FROM comments WHERE id = '$comment_id' AND user_id = '$user_id'";

        $this->execute($sql);

        $sql = "SELECT COUNT(id) AS comment_count FROM comments WHERE post_id = '$post_id'";

        $comment_count_arr = $this->select($sql, 1);

        $api_resp_data['comment_count'] = $comment_count_arr['comment_count'];

        $api_resp_data['error'] = false;

        return $api_resp_data;

    }

}