<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchCheckinsTable extends Migration
{
    // ここ変える感じで。
    private $tablename = 'match_checkins';

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
                $table->bigIncrements('id');
                $table->integer('match_id')->unsigned();
                $table->integer('team_id')->unsigned();
                $table->integer('user_id')->unsigned();
                $table->bigInteger('summoner_id')->unsigned()->default(0);
                $table->integer('champion_id')->unsigned()->default(0);
                $table->timestamps();
                $table->unique(['match_id','user_id']);
                $table->index('team_id', 'idx1');
                $table->index('summoner_id', 'idx2');
                $table->index('champion_id', 'idx3');
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
