<?php

namespace app\models;

/**
 * Form is used for updating existing comments or adding new comments
 */
class CommentForm extends \yii\base\Model {
   
    /**
     * Text of comment
     * @var string
     */
    public $content;
    
    /**
     * Comment id for updating content
     * @var integer
     */
    public $id;
    
    /**
     * Parent comment id
     * @var integer
     */
    public $parentId = 0;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['content', 'required', 'on' => ['create', 'update']],
            ['id', 'required', 'on' => ['update', 'delete']],
            ['parentId', 'required', 'on' => 'create'],
            ['parentId', 'integer', 'min' => 1, 'on' => 'create'],
            [['content'], 'string'],
        ];
    }
    
    /**
     * Saving comment
     * @return boolean
     */
    public function save() {
        if (!$this->validate()) {
            return false;
        }
        
        if ($this->scenario === 'update') {
            $comment = Comment::findOne(['id' => $this->id]);
            $comment->content = $this->content;
            if ($comment->save()) {
                return $comment;
            }
            $this->addErrors($comment->getErrors());
        } elseif ($this->scenario === 'delete') {
            $comment = Comment::findOne(['id' => $this->id]);
            if (!$comment) {
                $this->addError('id', 'Cannot find comment');
                return false;
            }
            return $comment->delete();
        } elseif ($this->scenario === 'create') {
            $comment = new Comment();
            $comment->content = $this->content;
            $comment->parentId = $this->parentId;
            if ($comment->save()) {
                return $comment;
            }
            $this->addErrors($comment->getErrors());
        }
        
        return false;
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'content' => 'Комментарий',
        ];
    }
}
