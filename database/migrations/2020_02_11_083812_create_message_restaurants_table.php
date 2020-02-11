<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessageRestaurantsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('message_restaurants', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('message', 300)->nullable();
			$table->string('image', 300)->nullable();
			$table->bigInteger('id_sender');
			$table->bigInteger('id_restaurant');
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
		Schema::drop('message_restaurants');
	}

}
