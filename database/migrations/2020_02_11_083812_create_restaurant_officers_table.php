<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRestaurantOfficersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('restaurant_officers', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('email');
			$table->string('password');
			$table->string('name');
			$table->string('gender');
			$table->string('image');
			$table->date('birth_date');
			$table->bigInteger('id_restaurant')->index('FK_restaurant_officers_restaurants');
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
		Schema::drop('restaurant_officers');
	}

}
