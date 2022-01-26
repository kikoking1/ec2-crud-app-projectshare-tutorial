<?php
class Post extends Db {

    public function get_feed($api_resp_data) {

        $conn = $this->con();

        $whereClause = '';

        if ( !empty($_GET['search']) ) { // They are searching for something

            $searchStr = mysqli_real_escape_string($conn, $_GET['search']);

            $whereClause = "WHERE
            posts.title LIKE '%$searchStr%'
            OR posts.description LIKE '%$searchStr%'
            OR CONCAT(users.firstname, ' ', users.lastname) LIKE '%$searchStr%'
            ";
        }

        $user_id = (int)$_SESSION['user_id'];

        $sql = "SELECT posts.id, posts.title, posts.description, posts.filename, posts.posted_time, posts.user_id, users.firstname, users.lastname, users.timezone,
        (SELECT COUNT(loves.id) FROM loves WHERE loves.post_id = posts.id) AS love_count,
        (SELECT COUNT(comments.id) FROM comments WHERE comments.post_id = posts.id) AS comment_count,
        IF( posts.user_id = '$user_id', 'true', 'false' ) AS user_owns,
        IF(posts.id IN (SELECT loves.post_id FROM loves WHERE loves.user_id = '$user_id'), 'true', 'false') AS is_loved
        FROM posts LEFT JOIN users ON posts.user_id = users.id
        $whereClause
        ORDER BY posts.posted_time DESC";

        $posts = $this->select($sql);

        // There are some posts returned, also get the comments
        if ( !empty($posts) ) {

            // ************************************************************
            // GET COMMENTS BY POSTS IDs, THEN ATTACH TO POSTS ARRAY

            $post_ids = array();

            foreach($posts as $post){
                $post_ids[] = $post['id'];
            }

            // (4,3,2,1)

            $sql = "SELECT comments.id, comments.comment, comments.posted_time, comments.post_id, users.username, users.firstname, users.lastname, users.timezone,
            IF(comments.user_id = '$user_id', 'true', 'false') AS user_owns
            FROM comments JOIN users ON comments.user_id = users.id
            WHERE comments.post_id IN (".implode(',', $post_ids).")
            ORDER BY posted_time DESC
            ";

            $comments = $this->select($sql);

            // Map<Int, ArrayList<Comment>> commentsByPostId = new Map<Int, ArrayList<Comment>>();
            $commentsByPostId = array();
            foreach($comments as $comment) {
                $post_id = $comment['post_id'];
                $commentsByPostId[$post_id][] = $comment;
            }

            // echo '<pre>'.print_r($commentsByPostId, 1).'</pre>';
            // die();
            
            // Attach comments to posts array
            for ( $x = 0; $x < count($posts); $x++ ) {

                $post_id = $posts[$x]['id'];

                // If post has coments, attach them to post
                if ( array_key_exists($post_id, $commentsByPostId) ) {

                    $posts[$x]['comments'] = $commentsByPostId[$post_id];

                } else { // No coments on this post. Attach empty comments array

                    $posts[$x]['comments'] = array();

                }

            }

            // echo '<pre>'.print_r($posts, 1).'</pre>';
            // die();
            
            $api_resp_data['posts'] = $posts;
            
        }

        $api_resp_data['error'] = false;

        return $api_resp_data;

    }

    public function get_by_id($id) {

        $conn = $this->con();

        $id = (int)$id;

        $sql = "SELECT * FROM posts WHERE id='$id'";

        $post = $this->select($sql, 1);

        return $post;

    }

    public function add($postArr) {

        $conn = $this->con();

        $title = mysqli_real_escape_string($conn, $postArr['title']);
        $description = mysqli_real_escape_string($conn, $postArr['description']);
        $user_id = (int)$_SESSION['user_id'];
        $posted_time = time();

        $file_upload = $this->save_img();
        $filename = $file_upload['filename'];

        if ( $file_upload['file_upload_error_status'] === 0 ) { // SUCCESS!!

            $sql = "INSERT INTO posts (title, description, filename, posted_time, user_id)
                    VALUES ('$title', '$description', '$filename', '$posted_time', '$user_id')";

            $this->execute($sql);

        } else { // FILE UPLOAD FAIL. Log error.
            $_SESSION['api_msg_errs'] = $file_upload['errors'];
        }

    }

    public function edit($postArr) {

        $conn = $this->con();

        // Sanitize post form field data
        $id = (int)$postArr['id'];
        $title = mysqli_real_escape_string($conn, $postArr['title']);
        $description = mysqli_real_escape_string($conn, $postArr['description']);
        $logged_in_user_id = (int)$_SESSION['user_id'];

        // Grab post details from db for requested post to edit, for cross checking purposes.
        $post = $this->get_by_id($id);

        // Check ownership of post is owned by logged in user.
        if ( !empty($post) && $post['user_id'] == $_SESSION['user_id'] ) {

            // Check file was submitted
            if ( !empty( $_FILES['fileToUpload']['name'] ) ) {

                $file_upload = $this->save_img();
                $filename = $file_upload['filename'];

                if ( $file_upload['file_upload_error_status'] === 0 ) { // File upload successful!

                    // Check file exists in img_uploads before trying to delete.
                    if ( !empty($post['filename']) ) {
                        if ( file_exists(APP_ROOT.'/html/assets/img_uploads/'.$post['filename']) ) {
                            
                            unlink(APP_ROOT.'/html/assets/img_uploads/'.$post['filename']);

                            $sql = "UPDATE posts SET title='$title', description='$description', filename='$filename' 
                            WHERE id='$id' 
                            AND user_id='$logged_in_user_id'";

                            $this->execute($sql);

                        } else { 
                            $_SESSION['api_msg_errs'][] = '* Old file does not exist.';
                        }
                    } else { 
                        $_SESSION['api_msg_errs'][] = '* Old file does not exist.';
                    }

                } else { // FILE UPLOAD FAIL. Log error.
                    $_SESSION['api_msg_errs'] = $file_upload['errors'];
                }

            } else {

                $sql = "UPDATE posts SET title='$title', description='$description' 
                WHERE id='$id' 
                AND user_id='$logged_in_user_id'";

                $this->execute($sql);

            }

        } else { 
            $_SESSION['api_msg_errs'][] = '* The post is not owned by you!!!!';
        }

    }

    public function delete($id) {

        $id = (int)$id;

        $post = $this->get_by_id($id);

        // Check ownership of post is owned by logged in user.
        if ( !empty($post) && $post['user_id'] == $_SESSION['user_id'] ) {

            // Check file exists in img_uploads before trying to delete.
            if ( !empty($post['filename']) ) {
                if ( file_exists(APP_ROOT.'/html/assets/img_uploads/'.$post['filename']) ) {
                    unlink(APP_ROOT.'/html/assets/img_uploads/'.$post['filename']);
                }
            }

            $sql = "DELETE FROM posts WHERE id='$id'";
            $this->execute($sql);
        }

    }

}
