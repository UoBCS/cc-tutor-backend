<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNonDetParserRunsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('non_det_parser_runs', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('content');
            $table->json('token_types');
            $table->json('grammar');
            $table->json('stack')->nullable();
            $table->integer('input_index')->default(0);
            $table->json('parse_tree')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('non_det_parser_runs');
    }
}
