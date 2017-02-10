<?php

// PUT: /comments/[id]/like
$app->put('/comments/:id/like', $auth(2), function($id) use ($app) {

    // Check if comment exists
    $comment = Comment::with(['likes' => function ($query) use ($app) {
                $query->where('user_id', $app->user_id);
        }])->find($id);

    // Comment not found, error
    if (!$comment) {
        $app->notFound();
    }

    // Set is_liked property
    $comment->is_liked();

    // Like the comment if not already liked
    if (!$comment->is_liked) { 
        $like = new Like(['user_id' => $app->user_id]);
        $liked = $comment->likes()->save($like);
    }

    // Tell the user it's liked
    $app->halt(200, json_encode(['message' => 'Comment liked!']));
  
});

// DELETE: /comments/[id]/like
$app->delete('/comments/:id/like', $auth(2), function($id) use ($app) {

    // Check if comment exists
    $comment = Comment::find($id);

    // Comment not found, error
    if (!$comment) {
        $app->notFound();
    }

    // Remove any likes for this comment + user combo
    $comment->likes()->where('user_id', $app->user_id)->delete();

    // Tell the user it's unliked
    $app->halt(200, json_encode(['message' => 'Comment unliked!']));
  
});

// DELETE: /comments/[id]
$app->delete('/comments/:id', $auth(2), function($id) use ($app) {

    // Check if comment exists
    $comment = Comment::find($id);

    // Comment not found, error
    if (!$comment) {
        $app->notFound();
    }

    // Check it belongs to current user
    if ($comment->user_id != $app->user_id) {
        $app->halt(401, json_encode(['message' => 'You can only delete comments that you created']));
    }

    // Remove all likes
    $comment->likes()->delete();

    // Remove comment
    $comment->delete();

    // Tell the user it's deleted
    $app->halt(200, json_encode(['message' => 'Comment deleted!']));
  
});
