<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('re_actions', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned()->autoIncrement()->unique();
            $table->uuid('uuid')->comment('store uuid of users in ghatreh');
            $table->integer('nid')->unsigned()->comment('store nid of products');
            $table->tinyInteger('status')->unsigned()->comment('store none = 0, like = 1, dislike = 2');
            $table->tinyInteger('change_number')->unsigned()->comment('store number of user reactions limit up to 5');
            $table->timestamps();
            $table->index('id');
            $table->index('uuid');
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
        Schema::dropIfExists('re_actions');
    }
}
