<?php

class Db {

    protected function con(){

        $env_vars = parse_ini_file(APP_ROOT."/env.ini", true);
        $conn = new mysqli(
            $env_vars['db']['servername'], 
            $env_vars['db']['username'], 
            $env_vars['db']['password'], 
            $env_vars['db']['dbname']
        );
        if($conn->connect_error){
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }

    protected function select($sql, $single = false){

        $conn = $this->con();
        $result = $conn->query($sql);
        $xssArr = array();
        if($result->num_rows > 0){

            $x = 0;
            while($row = $result->fetch_assoc()){

                foreach($row as $column => $value){
                    $xssArr[$x][$column] = htmlspecialchars($value, ENT_QUOTES);
                }

                $x++;

            }
        }

        $conn->close();

        if ( $single == true && !empty($xssArr) ) { //  if they passed the second param for a single record only
            $xssArr = $xssArr[0];
        }

        return $xssArr;

    }

    protected function execute($sql){

        $conn = $this->con();
        if($conn->query($sql) !== TRUE){
            echo "Your Statement: " . $sql . "<br><br>Error" . $conn->error;
            die('error with mysql execution');
        }
        $conn->close();

    }

    protected function execute_return_id($sql){

        $conn = $this->con();
        if($conn->query($sql) !== TRUE){
            echo "Your Statement: " . $sql . "<br><br>Error" . $conn->error;
            die('error with mysql execution');
        }
        $last_id = $conn->insert_id;

        $conn->close();
        return $last_id;

    }

    protected function save_img($target_dir = APP_ROOT."/html/assets/img_uploads/", $inputNameAttr = "fileToUpload"){

        // This function either returns an array of errors, or the filename on success
    
        $file_upload = array(
            'file_upload_error_status' => 0,
            'errors' => array(),
            'filename' => '',
        );
    
        if( !empty($_FILES[$inputNameAttr]["name"]) ){
    
            $filename = time() . basename($_FILES[$inputNameAttr]["name"]);
            $target_file = $target_dir . $filename;
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
            // Check if image file is a actual image or fake image
            if( !empty($_POST) ) {
                $check = getimagesize($_FILES[$inputNameAttr]["tmp_name"]);
                if($check !== false) {
                    $file_upload['file_upload_error_status'] = 0;
                } else {
                    $file_upload['file_upload_error_status'] = 1;
                    $file_upload['errors'][] = "File is not an image.";
                }
            }
            // Check if file already exists
            if (file_exists($target_file)) {
                $file_upload['file_upload_error_status'] = 1;
                $file_upload['errors'][] = "File already exists.";
            }
    
            // Check file size
            $allowedFileSize = 5000000; // in bytes
            if ($_FILES[$inputNameAttr]["size"] > $allowedFileSize) {
    
                $megabytesAllowed = $allowedFileSize / 1000000;
                $file_upload['file_upload_error_status'] = 1;
                $file_upload['errors'][] = "Your file is too large. Limit is $megabytesAllowed Megabytes.";
            }
            // Allow certain file formats
            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
                $file_upload['file_upload_error_status'] = 1;
                $file_upload['errors'][] = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
            // Check if $uploadOk is set to 1 by an error
            if ($file_upload['file_upload_error_status'] == 1) {
                $file_upload['file_upload_error_status'] = 1;
                $file_upload['errors'][] = "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES[$inputNameAttr]["tmp_name"], $target_file)) {
    
                    $file_upload['filename'] = mysqli_real_escape_string($this->con(), $filename);
    
                    return $file_upload; // File upload success
                } else {
                    $file_upload['file_upload_error_status'] = 1;
                    $file_upload['errors'][] = "An unknown error occured and your file could not be uploaded. It may be that your php.ini file needs to be configured for file uploading on this host.";
                }
            }
    
        }  //END IF FILE WAS SUBMITTED IN THE FORM
    
        // Comment this out after done testing
        // echo '<pre>' . print_r($file_upload, 1) . '</pre>';
        // die();
    
        return $file_upload;
    
    }

}
