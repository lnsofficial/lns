<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamsContactTable extends Migration
{
    // ここ変える感じで。
    private $tablename = 'teams_contact';

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
                $table->integer('team_id')->unsigned();
                $table->integer('user_id')->unsigned();
                $table->timestamps();
                $table->unique(['team_id','user_id']);
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
