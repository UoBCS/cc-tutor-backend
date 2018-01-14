<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParseTreeAttributeToLlRunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ll_runs', function (Blueprint $table) {
            $table->json('parse_tree');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ll_runs', function (Blueprint $table) {
            $table->dropColumn('parse_tree');
        });
    }
}
