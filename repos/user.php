<?php
class User extends Db {

    public function get_by_id($user_id) {

        $user_id = (int)$user_id;

        $sql = "SELECT id, username, firstname, lastname, bio, timezone FROM users WHERE id = '$user_id'";

        $user = $this->select($sql, 1);

        return $user;
    }

    public function add($postArr) {

        $conn = $this->con();

        $username = mysqli_real_escape_string($conn, $postArr['username']);
        $password = password_hash($postArr['password'], PASSWORD_DEFAULT);
        $firstname = mysqli_real_escape_string($conn, $postArr['firstname']);
        $lastname = mysqli_real_escape_string($conn, $postArr['lastname']);
        $bio = mysqli_real_escape_string($conn, $postArr['bio']);
        $timezone = mysqli_real_escape_string($conn, $postArr['timezone']);

        $sql = "INSERT INTO `users` (username, password, firstname, lastname, bio, timezone)
                VALUES ('$username','$password','$firstname','$lastname','$bio','$timezone')";

        $new_user_id = $this->execute_return_id($sql);

        return $new_user_id;

    }

    public function edit($user_id, $postArr) {

        $conn = $this->con();
        $user_id = (int)$user_id;
        $username = mysqli_real_escape_string($conn, $postArr['username']);
        $password = password_hash($postArr['password'], PASSWORD_DEFAULT);
        $firstname = mysqli_real_escape_string($conn, $postArr['firstname']);
        $lastname = mysqli_real_escape_string($conn, $postArr['lastname']);
        $bio = mysqli_real_escape_string($conn, $postArr['bio']);
        $timezone = mysqli_real_escape_string($conn, $postArr['timezone']);

        $sql = "UPDATE users 
        SET username='$username', password='$password', firstname='$firstname', lastname='$lastname', bio='$bio', timezone='$timezone'
        WHERE id='$user_id'";

        if ( empty( trim($postArr['password']) ) ) { // if password IS EMPTY, don't try to update it.

            $sql = "UPDATE users 
            SET username='$username', firstname='$firstname', lastname='$lastname', bio='$bio', timezone='$timezone'
            WHERE id='$user_id'";

        }

        $this->execute($sql);

    }

    public function exists($username) {

        $conn = $this->con();

        $username = mysqli_real_escape_string($conn, $username);

        $sql = "SELECT id FROM users WHERE username = '$username'";

        $user = $this->select($sql);

        if ( !empty($user) ) { // User exists
            return true;
        } else { // User DOES NOT exist
            return false;
        }

    }

    public function login($postArr) {

        $conn = $this->con();

        $username = mysqli_real_escape_string($conn, $postArr['username']);
        $sql = "SELECT `id`, `password` FROM `users` WHERE `username` = '$username'";

        $user = $this->select($sql, 1);

        if ( password_verify($postArr['password'], $user['password']) ) { //  They used correct password! Log them in!!!!
            
            $_SESSION['user_id'] = $user['id'];
            return true;
        } else {
            return false;
        }

    }



}
