<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration
{
    // ここ変える感じで。
    private $tablename = 'sessions';

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
                $table->string('id');
                $table->string('session');
                $table->timestamp('create_date')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->timestamp('update_date')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
                $table->primary('id');
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
