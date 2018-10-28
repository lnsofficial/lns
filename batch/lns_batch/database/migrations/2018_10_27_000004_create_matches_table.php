<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchesTable extends Migration
{
    // ここ変える感じで。
    private $tablename = 'matches';

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
                $table->integer('host_team_id')->unsigned();
                $table->integer('apply_team_id')->unsigned()->nullable()->default(null);
                $table->dateTime('match_date')->nullable()->default(null);
                $table->dateTime('recruit_start_date')->nullable()->default(null);
                $table->dateTime('deadline_date')->nullable()->default(null);
                $table->tinyInteger('stream')->unsigned()->nullable()->default(null);
                $table->tinyInteger('type')->unsigned();
                $table->tinyInteger('state')->unsigned();
                $table->integer('winner')->unsigned()->nullable()->default(null);
                $table->string('screen_shot_url')->nullable()->default(null);
                $table->string('tournament_code')->nullable()->default(null);
                $table->timestamps();
                $table->index(['host_team_id','apply_team_id','winner'], 'hti');
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
