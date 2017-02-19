<?php

use yii\db\Migration;
use yii\db\Schema;

class m170217_180836_create_comment_table extends Migration
{
    const TABLE_NAME = 'comment';
    
    public function safeUp()
    {
        $this->createTable(self::TABLE_NAME, [
            'id' => Schema::TYPE_PK,
            'content' => Schema::TYPE_STRING . '(1000) NOT NULL',
            'left_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'right_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'level' => Schema::TYPE_INTEGER . ' NOT NULL',
            'created_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT now()',
            'updated_at' => Schema::TYPE_TIMESTAMP . ' NOT NULL DEFAULT now()',
        ]);
        
        $this->createIndex(
            'comment_idx', 
            self::TABLE_NAME, 
            ['left_id', 'right_id', 'level']
        );
        
        // root node of the tree
        $this->insert(self::TABLE_NAME, [
            'id' => 1,
            'left_id' => 1,
            'right_id' => 2,
            'level' => 0,
            'content' => '<root node>',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable(self::TABLE_NAME);
    }
}
