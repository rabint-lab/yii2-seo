<?php

use yii\db\Migration;

class m210823_104739_create_table_seo_option extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%seo_option}}', [
            'id' => $this->primaryKey()->comment('شناسه'),
            'route' => $this->string()->comment('بخشی از مسیر صفحه '),
            'type' => $this->integer()->comment('نوع متا'),
            'name' => $this->string()->comment('نام'),
            'content' => $this->text()->comment('محتوا'),
            'location' => $this->integer()->comment('محل اضافه شدن (هدر یا فوتر)'),
            'linked' => $this->string()->comment('لینک شده'),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ], $tableOptions);

    }

    public function down()
    {
        $this->dropTable('{{%seo_option}}');
    }
}
