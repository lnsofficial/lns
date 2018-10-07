<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ロゴステータス
        if( !Schema::hasColumn('teams', 'logo_status') )
        {
            Schema::table('teams', function (Blueprint $table) {
                $table->unsignedTinyInteger('logo_status')->default(0)->after('status');
            });
        }
        // ロゴ更新日時
        if( !Schema::hasColumn('teams', 'logo_updated_at') )
        {
            Schema::table('teams', function (Blueprint $table) {
                $table->timestamp('logo_updated_at')->nullable()->after('logo_status');
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
        if( Schema::hasColumn('teams', 'logo_status') )
        {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('logo_status');
            });
        }
        if( Schema::hasColumn('teams', 'logo_updated_at') )
        {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('logo_updated_at');
            });
        }
    }
}
