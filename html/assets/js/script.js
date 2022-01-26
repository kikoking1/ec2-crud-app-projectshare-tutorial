$(document).ready(function(){

    // Populate posts feed on page load
    get_post_feed('');

    // Search posts form submit listener
    $(document).on('submit', '#search-posts-form', function(e){
        e.preventDefault();

        var search = $('#search-posts-form-input').val();
        get_post_feed(search);

    });

    // Clear Search btn listener
    $(document).on('click', '.clear-search-btn', function(){
        
        $('#search-posts-form-input').val('');
        get_post_feed('');

    });

    // Initialize chosen select form fields, on any select box that has this class.
    $(".chosen-select").chosen();

    // Guess user timezone, and auto pick that timezone in timezone select box.
    if( $('#signup-timezone-select') ){

        var tz = moment.tz.guess();

        $('#signup-timezone-select option').each(function(){

            if( $(this).val() == tz ){
                $(this).prop('selected', true);
            }
        });

        // Reinitialize chosen form field
        $('#signup-timezone-select').trigger("chosen:updated");

    }

    // Love button listener
    $(document).on('click', '.love-btn', function(){

        // Elements
        var $love_btn = $(this);
        var $loves_count = $love_btn.find('.loves-count');
        var $love_icon = $love_btn.find('.love-icon');

        var post_id = $love_btn.closest('.post-body').attr('data-postid');

        $.post('/api/loves/add.php', {"post_id":post_id}, function(apiData){

            var apiData = JSON.parse(apiData);

            $loves_count.text(apiData.love_count);
            
            if ( apiData.loved_state == 'loved' ) {
                $love_icon.removeClass('far').addClass('fas');
            } else if ( apiData.loved_state == 'unloved' ) {
                $love_icon.removeClass('fas').addClass('far');
            }


        });

    }); // Love button listener


    // Comment Form Submit Listener
    $(document).on('submit', '.comment-form', function(e){
        e.preventDefault();

        // Elements
        var $commentForm = $(this);
        var $commentBox = $commentForm.find('.comment-box');
        var $commentLoop = $commentForm.closest('.post-body').find('.comment-loop');
        var $commentCount = $commentForm.closest('.post-body').find('.comment-count');

        // Values
        var post_id = $commentForm.closest('.post-body').attr('data-postid');
        var comment = $.trim($commentBox.val());

        if ( comment.length > 0 ) {

            $.post('/api/comments/add.php', {"post_id" : post_id, "comment" : comment}, function(apiData){

                apiData = JSON.parse(apiData);

                console.log(apiData);

                if ( apiData.error == false ) {

                    var commentLoopHtml = '';
                    $.each(apiData.comments, function(index, comment){

                        commentLoopHtml += `
                            <div class="user-comment">
                        `;

                        if ( comment.user_owns == 'true' ) {
                            commentLoopHtml += `
                                <i class="fas fa-times-circle delete-comment-btn" data-commentid="${comment.id}"></i>
                            `;
                        }

                        commentLoopHtml += `
                                <p>
                                    <strong>${comment.firstname} ${comment.lastname}</strong>
                                    ${comment.comment}
                                </p>
                                ${moment.unix(comment.posted_time).tz(comment.timezone).fromNow(true)}
                            </div>
                        `;

                    });

                    $commentLoop.html(commentLoopHtml);

                    $commentBox.val('');

                    // Update comment count
                    $commentCount.text(apiData.comment_count);

                }



            });

        }

    }); // Comment Form Submit Listener


    // Comment Delete Button Listener    
    $(document).on('click', '.delete-comment-btn', function(){

        // Elements
        var $delCommentBtn = $(this);
        var $userComment = $delCommentBtn.closest('.user-comment');
        var $commentCount = $delCommentBtn.closest('.post-body').find('.comment-count');

        // Values
        var post_id = $delCommentBtn.closest('.post-body').attr('data-postid');
        var comment_id = $delCommentBtn.attr('data-commentid');

        $.post('/api/comments/delete.php', {"comment_id" : comment_id, "post_id" : post_id}, function(apiData){

            var apiData = JSON.parse(apiData);

            if ( apiData.error == false ) {
                // delete comment from screen
                $userComment.remove();

                // Update comment count
                $commentCount.text(apiData.comment_count);
            }

        });

    });


    // Hide/show extra comments listener
    $(document).on('click', '.see-more-comments', function(){

        // Elements
        var $seeMoreCommentsBtn = $(this);
        var $comment_loop = $seeMoreCommentsBtn.siblings('.comment-loop');

        if ( $comment_loop.hasClass('extra-comments-hidden') ) {
            $comment_loop.removeClass('extra-comments-hidden');
            $seeMoreCommentsBtn.text('show less comments -');
        } else {
            $comment_loop.addClass('extra-comments-hidden');
            $seeMoreCommentsBtn.text('show all comments +');
        }

    });


}); // END DOCUMENT READY

function get_post_feed(search) {

    search = $.trim(search);
    $('#search-message').html('');

    $.get('/api/posts/get_feed.php', {"search" : search}, function(apiData){
        
        apiData = JSON.parse(apiData);

        if ( apiData.error == false ) {

            var postsFeedHtml = '';

            // If they search, and didn't return results
            if ( search.length > 0 && apiData.posts.length === 0 ) {

                $('#search-message').html(`
                    <p class="text-default text-center mt-5">
                        Sorry! Your search did not return any results...
                        <br>
                        <span class="clear-search-btn text-info text-center">Clear search</span>
                    </p>
                `);

            // No posts exist yet, and they are NOT searching 
            } else if ( search.length === 0 && apiData.posts.length === 0 ) {

                $('#search-message').html(`
                    <p class="text-default text-center mt-5">
                        No posts exist yet! <br>Create one in the form above!
                    </p>
                `);

            // THEY ARE SEARCHING! Display their search string above the posts feed
            } else if ( search.length > 0 && apiData.posts.length > 0 ) {

                $('#search-message').html(`
                    <h3 class="mt-3">
                        Searching: "${search}"
                    </h3>
                `);

            }

            $.each(apiData.posts, function(index, post){

                // Maybe this part would be better in React?

                postsFeedHtml += `
                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="clearfix">
                        ${post.firstname} ${post.lastname}
                `;

                if ( post.user_owns == 'true' ) {
                    postsFeedHtml += `
                        <!-- edit and delete icons (pencil and trash) -->
                        <span class="float-right">
                            <a class="text-info" href="/posts/edit.php?id=${post.id}">
                                <i class="far fa-edit" aria-hidden="true"></i>
                            </a>
                            <a class="text-danger" href="/api/posts/delete.php?id=${post.id}">
                                <i class="far fa-trash-alt" aria-hidden="true"></i>
                            </a>
                        </span>
                    `;
                }

                postsFeedHtml += `
                        </h4>
                    </div><!-- .card-header -->

                    <div class="card-body post-body" data-postid="${post.id}">
                    
                        <img class="img-fluid" src="/assets/img_uploads/${post.filename}">

                        <br><br>

                        <h5>${post.title}</h5>
                        <p>${post.description}</p>

                        <p>Posted on: ${moment.unix(post.posted_time).tz(post.timezone).format("YYYY MMMM D, h:mm a")}</p>

                        <div class="love-btn float-left">
                            <i class="${post.is_loved == 'true' ? 'fas' : 'far'} fa-heart text-danger love-icon"></i> <span class="loves-count">${post.love_count}</span>
                        </div>

                        <div class="float-right">
                            <i class="far fa-comment"></i> <span class="comment-count">${post.comment_count}</span>
                        </div>

                        <div class="clearfix"></div>

                        <!-- COMMENT FORM -->
                        <form class="mt-3 comment-form">
                            <input class="form-control comment-box" type="text" name="comment" placeholder="Write a comment...">
                        </form>

                        <!-- COMMENTS LOOP -->
                        <div class="comment-loop extra-comments-hidden">
                            
                        `;
                        
                        $.each(post.comments, function(index, comment){

                            postsFeedHtml += `
                            <div class="user-comment">
                            `;

                            if (comment.user_owns == 'true') {
                                postsFeedHtml += `
                                    <i class="fas fa-times-circle delete-comment-btn" data-commentid="${comment.id}"></i>
                                `;
                            }

                            postsFeedHtml += `
                                <p>
                                    <strong>${comment.firstname} ${comment.lastname}</strong>
                                    ${comment.comment}
                                </p>
                            ${moment.unix(comment.posted_time).tz(comment.timezone).fromNow(true)}
                            </div><!-- .user-comment -->
                            `;
                        });

                        postsFeedHtml += `
                        </div><!-- .comment-loop -->
                        `;

                        if ( post.comments.length > 5 ) {
                            postsFeedHtml += `<div class="see-more-comments text-info pt-2">show all comments +</div>`;
                        }

                postsFeedHtml += `
                    </div><!-- .card-body -->
                </div><!-- .card -->
                
                `;

            }); // POST LOOP END

            $('#post-feed').html(postsFeedHtml);

        }

    });

}

function previewFile() {

    var preview = document.getElementById('img-preview');
    var file = document.getElementById('file-with-preview').files[0];

    var reader = new FileReader();

    reader.onloadend = function(){
        preview.src = reader.result;
    }

    if(file) {
        reader.readAsDataURL(file);
    }else{
        preview.src = "";
    }

}