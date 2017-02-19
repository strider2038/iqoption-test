<?php

namespace app\models;

use Yii;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "comment".
 *
 * @property integer $id
 * @property string $content
 * @property integer $left_id
 * @property integer $right_id
 * @property integer $level
 * @property static[] $nodes
 * @property integer $nodesCount
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * @var static[]
     */
    public $nodes = [];
    
    /**
     * Subtree nodes count
     * @var integer
     */
    protected $_nodesCount = 0;
    
    /**
     * Parent id, required for adding new comments
     * @var integer
     */
    public $parentId;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'left_id', 'right_id', 'level'], 'required'],
            [['left_id', 'right_id', 'level'], 'integer'],
            [['content'], 'string', 'max' => 1000],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'Content',
            'left_id' => 'Left ID',
            'right_id' => 'Right ID',
            'level' => 'Level',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('NOW()'),
            ],
        ];
    }
    
    /**
     * @inheritdoc
     * @return CommentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CommentQuery(get_called_class());
    }
    
    /**
     * Getter for nodes count
     * @return integer
     */
    public function getNodesCount() {
        return $this->_nodesCount;
    }
    
    /**
     * Inner setter for nodes count (nodes count is calculated by CommentQuery)
     * @param integer $value
     */
    protected function setNodesCount($value) {
        $this->_nodesCount = (int) $value;
    }
    
    /**
     * Replacing insert method for dealing with nested sets logic
     * @inheritdoc
     */
    public function insert($runValidation = true, $attributes = null) {
        if ($runValidation && !$this->validate(['content'])) {
            Yii::info('Model not inserted due to validation error.', __METHOD__);
            return false;
        }
        $parent = Comment::findOne(['id' => $this->parentId]);
        if (!$parent) {
            $this->addError('parentId', 'Parent node not found!');
            return false;
        }
        
        $right = $parent->right_id;
        $comment = $this;
        $comment->left_id = $right;
        $comment->right_id = $right + 1;
        $comment->level = $parent->level + 1;
        static::getDb()->transaction(function() use (&$comment, $right) {
            Comment::updateAll([
                'left_id' => new Expression('left_id + 2'),
                'right_id' => new Expression('right_id + 2')
            ], ['>', 'left_id', $right]);
            Comment::updateAll(
                ['right_id' => new Expression('right_id + 2')],
                ['AND', ['<', 'left_id', $right], ['>=', 'right_id', $right]]
            );
            $comment->insertInternal();
        });
        return true;
    }
    
    /**
     * Replacing delete method for dealing with nested sets logic
     * @inheritdoc
     */
    public function delete() {
        $comment = $this;
        $width = $comment->right_id - $comment->left_id + 1;
        static::getDb()->transaction(function() use(&$comment, $width) {
            Comment::deleteAll(['BETWEEN', 'left_id', $comment->left_id, $comment->right_id]);
            Comment::updateAll(
                ['right_id' => new Expression("right_id - {$width}")], 
                ['>', 'right_id', $comment->right_id]
            );
            Comment::updateAll(
                ['left_id' => new Expression("left_id - {$width}")], 
                ['>', 'left_id', $comment->right_id]
            );
        });
        return true;
    }
    
    /**
     * Returns comments array as a tree
     * @param type $level
     * @return Comment[]
     */
    static public function getTree() {
        $list = static::find()->orderBy('left_id')->all();
        $count = count($list);
        if ($count <= 0) {
            return [];
        }
        $tree = $list[0]; // root node
        $stack = [$list[0]];
        $currentLevel = $list[0]->level;
        for ($i = 1; $i < $count; $i++) {
            $node = $list[$i];
            $prev = $list[$i-1];
            $levelDiff = $node->level - $currentLevel;
            if ($levelDiff === 1) {
                $stack[count($stack) - 1]->nodes[] = $node;
            } elseif ($levelDiff > 1) {
                array_push($stack, $prev);
                $currentLevel = $prev->level;
                $prev->nodes[] = $node;
            } else {
                $shift = 1 - $levelDiff;
                $stack = array_slice($stack, 0, count($stack) - $shift);
                $stack[count($stack) - 1]->nodes[] = $node;
                $currentLevel = $node->level - 1;
            }
        }
        return $tree;
    }
    
}
