<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\bootstrap\Html;
use app\models\Comment;
use app\models\CommentForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     * @return string
     */
    public function actionIndex()
    {
        // Root node of the tree
        $commentsTree = Comment::getTree();
        return $this->render('index', compact('commentsTree'));
    }
    
    /**
     * Processing comment actions by AJAX
     * @return type
     */
    public function actionAjaxCommentSave() {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        // AJAX response format
        $response = [
            'ok' => false,
            'data' => [],
            'errors' => [],
        ];
        
        // loading and saving the form
        $model = new CommentForm();
        $model->scenario = Yii::$app->request->post('scenario', false);
        $model->attributes = Yii::$app->request->post();
        
        if (!$comment = $model->save()) {
            $response['errors'] = $model->getErrors();
            return $response;
        }
        
        // returning content of the comment card
        $response['ok'] = true;
        if ($comment instanceof Comment) {
            $response['data'] = ['content' => $model->scenario === 'create'
                    ? $this->renderPartial('_comment', compact('comment'))
                    : Html::encode($comment->content)];
        }
        return $response;
    }
}
