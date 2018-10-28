<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    // ここ変える感じで。
    private $tablename = 'settings';

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
                $table->string('name');
                $table->string('value');
                $table->timestamps();
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
