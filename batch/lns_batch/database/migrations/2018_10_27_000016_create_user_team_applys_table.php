<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTeamApplysTable extends Migration
{
    // ここ変える感じで。
    private $tablename = 'user_team_applys';

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
                $table->integer('user_id')->unsigned();
                $table->integer('team_id')->unsigned();
                $table->tinyInteger('type')->unsigned()->comment('1:memberとして、2:連絡先として、3:staffとして');
                $table->tinyInteger('state')->unsigned()->comment('1:申請中、2:キャンセル、3:受諾、4:拒否');
                $table->timestamps();
                $table->softDeletes();
                $table->index('user_id');
                $table->index('team_id');
                $table->index('type');
                $table->index('state');
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
