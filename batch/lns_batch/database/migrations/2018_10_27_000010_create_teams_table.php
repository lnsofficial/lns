<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsTable extends Migration
{
    // ここ変える感じで。
    private $tablename = 'teams';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if( !Schema::hasTable( $this->tablename ) )
        {
            Schema::create($this->tablename, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('team_name');
                $table->string('team_name_kana');
                $table->string('team_tag');
                $table->string('team_tag_kana');
                $table->text('comment');
                $table->tinyInteger('status')->unsigned()->default(0);
                $table->tinyInteger('logo_status')->unsigned()->default(0);
                $table->timestamp('logo_updated_at')->nullable()->default(null);
                $table->timestamps();
                $table->unique('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists( $this->tablename );
    }
}
