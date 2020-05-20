<?php
declare(strict_types = 1);

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration as BaseMigration;

/**
 * Class Group
 */
class GroupTable extends BaseMigration
{
    const TABLE = 'group';

    /**
     * @return void
     */
    public function up() : void
    {
        Schema::create(self::TABLE, function (Blueprint $blueprint)
        {
            $blueprint->comment('群');
            $blueprint->increments('group_id')->comment('主键');
            $blueprint->integer('user_id', false, true, 11)->comment('所属用户');
            $blueprint->char('group_name', 30)->comment('群名');
            $blueprint->string('avatar', 255)->comment('群头像');
            $blueprint->enum('size', [200, 500, 1000])->comment('群规模');
            $blueprint->text('introduction')->comment('群介绍');
            $blueprint->tinyInteger('validation', false, true, 1)->comment('加群验证 0 不需要 1 需要');
            $blueprint->timestamps();
            //            $blueprint->tinyInteger('delete_flag', false, true, 1)->default(0)->comment('软删除 0正常 1删除');
            $blueprint->softDeletes()->comment('删除时间 为NULL未删除');
            $blueprint->index('user_id');
            $blueprint->engine  = 'Innodb';
            $blueprint->charset = 'utf8mb4';
        });
    }

    /**
     * @return void
     */
    public function down() : void
    {
        Schema::drop(self::TABLE);
    }
}
