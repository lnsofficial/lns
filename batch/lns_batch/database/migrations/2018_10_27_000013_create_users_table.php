<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // ここ変える感じで。
    private $tablename = 'users';

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
                $table->string('login_id')->nullable()->default(null);
                $table->string('password');
                $table->string('summoner_id')->nullable()->default(null);
                $table->string('account_id')->nullable()->default(null);
                $table->string('puuid')->nullable()->default(null);
                $table->string('summoner_name');
                $table->string('summoner_name_kana')->nullable()->default(null);
                $table->string('discord_id');
                $table->tinyInteger('main_role')->unsigned()->comment('1:Top,2:Jungle,3:Mid,4:ADC,5:Support');
                $table->string('comment')->nullable()->default(null);
                $table->timestamps();
                $table->unique('discord_id');
                $table->unique('login_id');
                $table->unique('summoner_id');
                $table->unique('account_id');
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
