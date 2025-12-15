<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apple}}`.
 */
class m251214_220148_create_apple_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%apple}}', [
            'id' => $this->primaryKey(),
            'color' => $this->string(20)->notNull(),
            'appear_date' => $this->integer()->notNull(),
            'fall_date' => $this->integer(),
            'status' => $this->smallInteger()->notNull()->defaultValue(1),
            'size' => $this->decimal(3, 2)->notNull()->defaultValue(1.00),
            'eaten_percent' => $this->decimal(5, 2)->notNull()->defaultValue(0.00),
            'is_spoiled' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-apple-status', '{{%apple}}', 'status');
        $this->createIndex('idx-apple-is_spoiled', '{{%apple}}', 'is_spoiled');
    }

    public function safeDown()
    {
        $this->dropTable('{{%apple}}');
    }
}
