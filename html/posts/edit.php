<?php  require_once("../../core/init.php");

    /* 
    ============================================
    GET PROJECT RECORD TO PRE POPULATE EDIT FORM
    ============================================
    */

    // Check url has an id in it
    if ( !empty($_GET['id']) ) { 

        $p = new Post;
        $post = $p->get_by_id($_GET['id']);

        // if they don't own the post, or the post doesn't exist, redirect away.
        if ( empty( $post ) || $post['user_id'] != $_SESSION['user_id'] ) {
            header("Location: /posts/");
            exit();
        }


    } else { // no id was passed in the url. Redirect away.
        header("Location: /posts/");
        exit();
    }


    (function(){
        // set dynamic vars to pass to html_head.php
        $title = 'Edit Post Page';
        $description = '';
        require_once(APP_ROOT."/elements/html_head.php");
        require_once(APP_ROOT."/elements/nav.php");
    })();

?>


<div class="container">


    <div class="row">
        <div class="col-md-8">
            <div class="card border-success mt-3">

                <div class="card-header">
                    <h4>Edit Post:</h4>
                </div><!-- .card-header -->

                <div class="card-body">

                    <?php // Display error message
                    if ( !empty($_SESSION['api_msg_errs']) ) {

                        echo '<p class="text-danger">';

                            foreach ( $_SESSION['api_msg_errs'] as $error ) {
                                echo $error;
                                echo '<br>';
                            }

                        echo '</p>';

                        unset($_SESSION['api_msg_errs']);
                    }
                    ?>

                    <form action="/api/posts/edit.php" method="post" enctype="multipart/form-data">

                        <input type="hidden" name="id" value="<?=$post['id']?>">

                        <img id="img-preview" src="/assets/img_uploads/<?=$post['filename']?>">

                        <div class="form-group">
                            <input id="file-with-preview" type="file" name="fileToUpload" onchange="previewFile()">
                        </div>

                        <div class="form-group">
                            <input class="form-control" type="text" name="title" value="<?=$post['title']?>" placeholder="Post Title:" required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" name="description" placeholder="Post Description:" required><?=$post['description']?></textarea>
                        </div>

                        <input class="btn btn-success" type="submit" value="Submit">

                    </form>

                </div><!-- .card-body -->

            </div><!-- .card -->
        </div><!-- .col-md-8 -->
    </div><!-- .row -->

</div><!-- .container -->





<?php require_once(APP_ROOT."/elements/footer.php");