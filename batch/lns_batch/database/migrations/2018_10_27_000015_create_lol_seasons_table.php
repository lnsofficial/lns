<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLolSeasonsTable extends Migration
{
    // ここ変える感じで。
    private $tablename = 'lol_seasons';

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
                $table->string('season');
                $table->timestamp('start_at');
                $table->timestamp('end_at');
                $table->timestamps();
                $table->unique('season');
                $table->index(['start_at','end_at']);
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
