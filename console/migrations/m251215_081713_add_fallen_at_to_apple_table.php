<?php

use yii\db\Migration;

class m251215_081713_add_fallen_at_to_apple_table extends Migration
{
      public function safeUp()
    {
        $this->addColumn('{{%apple}}', 'fallen_at', $this->integer()->null()->after('created_at'));
        $this->addColumn('{{%apple}}', 'rotten_at', $this->integer()->null()->after('fallen_at'));
        $this->createIndex('idx-apple-fallen-at', '{{%apple}}', 'fallen_at');
        $this->createIndex('idx-apple-rotten-at', '{{%apple}}', 'rotten_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-apple-rotten-at', '{{%apple}}');
        $this->dropIndex('idx-apple-fallen-at', '{{%apple}}');
        $this->dropColumn('{{%apple}}', 'rotten_at');
        $this->dropColumn('{{%apple}}', 'fallen_at');
    }

 

}
