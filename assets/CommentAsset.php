<?php

namespace app\assets;

/**
 * 
 */
class CommentAsset extends \yii\web\AssetBundle {
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/comment.css'
    ];
    public $js = [
        'js/models/comment-model.js',
        'js/views/comment-view.js',
        'js/controllers/comment-manager.js',
        'js/controllers/comment-page-controller.js',
    ];
    public $depends = [
        'app\assets\AppAsset',
    ];
}
