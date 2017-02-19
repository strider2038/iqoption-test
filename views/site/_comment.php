<?php 

use yii\bootstrap\Html;

/* @var $this yii\web\View */
/* @var $comment app\models\Comment */
?>
<div class="js-comment <?= $comment->level > 1 ? 'comment__margin' : '' ?>"
     data-id="<?= $comment->id ?>" 
     data-level="<?= $comment->level ?>">
    <div class="panel panel-default comment js-comment__body">
        <a href="javascript:void(0);" class="comment__toggle-tree js-comment__toggle-tree">
            <?= Html::icon('chevron-' . ($comment->level == 1 ? 'right' : 'down')) ?>
        </a>
        <div class="panel-body js-comment__content"><?= Html::encode($comment->content) ?></div>
        <div class="panel-footer text-right">
            <div class="js-comment__errors text-danger"></div>
            <div class="comment__actions-edit">
                <a href="javascript:void(0);" class="js-comment__save">Сохранить</a>
                <span class="comment__bullet">&bull;</span>
                <a href="javascript:void(0);" class="text-danger js-comment__cancel">Отмена</a>
            </div>
            <div class="comment__actions-default">
                <a href="javascript:void(0);" class="text-danger js-comment__delete">Удалить</a>
                <span class="comment__bullet">&bull;</span>
                <a href="javascript:void(0);" class="js-comment__edit">Редактировать</a>
                <span class="comment__bullet">&bull;</span>
                <a href="javascript:void(0);" class="js-comment__reply">Ответить</a>
            </div>
        </div>
    </div>
    <div class="js-comment__children" <?= $comment->level == 1 ? 'style="display: none;"' : '' ?>>
        <?php foreach ($comment->nodes as $node) {
            echo $this->render('_comment', ['comment' => $node]);
        } ?>
    </div>
</div>