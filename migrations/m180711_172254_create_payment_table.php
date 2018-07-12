<?php

use yii\db\Migration;

/**
 * Handles the creation of table `payment`.
 */
class m180711_172254_create_payment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('payment', [
            'id' => $this->primaryKey(),
            'email' => $this->string()->notNull(),
            'sum' => $this->integer(),
            'currency' => $this->string(3),
            'source' => $this->smallInteger()->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('payment');
    }
}
