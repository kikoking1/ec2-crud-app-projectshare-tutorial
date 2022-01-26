<?php  require_once("../../core/init.php");
    (function(){
        // set dynamic vars to pass to html_head.php
        $title = 'Posts Page';
        $description = '';
        require_once(APP_ROOT."/elements/html_head.php");
        require_once(APP_ROOT."/elements/nav.php");
    })();

?>

<div class="container">

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

    <div class="row">
        <div class="col-md-8 order-last order-md-first">
        
            <!-- ADD PROJECT FORM -->
            <div class="card border-success mt-3">
            
                <div class="card-header">
                    <h4>Share New Post:</h4>
                </div><!-- .card-header -->

                <div class="card-body">

                    <form action="/api/posts/add.php" method="post" enctype="multipart/form-data">

                        <img id="img-preview">

                        <div class="form-group">
                            <input id="file-with-preview" type="file" name="fileToUpload" onchange="previewFile()" required>
                        </div>
                        <div class="form-group">
                            <input class="form-control" type="text" name="title" placeholder="Post Title:" required>
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" name="description" placeholder="Post Description:" required></textarea>
                        </div>
                    
                        <input class="btn btn-success" type="submit" value="Submit">

                    </form>

                </div><!-- .card-body -->


            </div><!-- .card -->

            <!-- POSTS LOOP (injected by ajax) -->
            <div id="search-message"></div>
            <div id="post-feed"></div>

        </div><!-- .col-md-8 -->

        <div class="col-md-4 order-first order-md-last">
        
            <!-- SEARCH BAR -->
            <div class="card border-info mt-3">
                <div class="card-header">
                    <h4>Search Posts</h4>
                </div><!-- .card-header -->

                <div class="card-body">
                    
                    <form id="search-posts-form" action="/posts/" method="get">
                            
                        <div class="form-group">
                            <label>Search</label>
                            <input id="search-posts-form-input" type="search" class="form-control" name="search" required>
                        </div>

                        <input type="submit" value="Submit">

                        <div class="mt-3">
                            <span class="clear-search-btn text-info">Clear search</span>
                        </div>
                        
                    </form>

                </div><!-- .card-body -->
            </div><!-- .card -->
            
        </div><!-- .col-md-4 -->


    </div><!-- .row -->

</div><!-- .container -->




<?php require_once(APP_ROOT."/elements/footer.php");
