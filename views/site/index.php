<?php

/* @var $this yii\web\View */
/* @var $commentsTree app\models\Comment The root node of the tree */

$this->title = 'Comments';

\app\assets\CommentAsset::register($this);

$this->registerJs('commentPageController = new CommentPageController();');
?>
<div class="site-index js-comment-wrapper">
    <?php foreach ($commentsTree->nodes as $comment) {
        echo $this->render('_comment', compact('comment'));
    } ?>
</div>
