<?php

namespace app\models;

use yii\db\Query;

/**
 * This is the ActiveQuery class for [[Comment]].
 *
 * @see Comment
 */
class CommentQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return Comment[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Comment|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
    
    /**
     * Additionaly calculating count of subnodes of every node in a query
     */
    public function init() {
        parent::init();
        
        $countQuery = (new Query())->select([
                'node_id' => 'parent.id', 
                'count' => 'count(*)'
            ])
            ->from(['child' => Comment::tableName()])
            ->innerJoin(
                ['parent' => Comment::tableName()], 
                'parent.left_id < child.left_id AND parent.right_id > child.right_id'
            )
            ->groupBy('parent.id');
        
        $this->leftJoin(['c' => $countQuery], 'comment.id = c.node_id')
            ->addSelect(['comment.*', 'nodesCount' => 'c.count']);
    }
}
