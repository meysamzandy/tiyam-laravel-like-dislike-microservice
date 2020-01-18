<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTotalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('totals', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned()->autoIncrement()->unique();
            $table->integer('nid')->unsigned()->unique()->comment('store nid of products');
            $table->unsignedInteger('like')->default(0)->comment('store number of like');
            $table->unsignedInteger('dislike')->default(0)->comment('store number of dislike');
            $table->unsignedInteger('total')->default(0)->comment('store total of all reactions');
            $table->float('wilson',5,4)->comment('store wilson');
            $table->timestamps();
            $table->index('id');
            $table->index('nid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('totals');
    }
}
