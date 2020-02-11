<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOpeningHoursTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('opening_hours', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('id_restaurant')->index('FK_opening_hours_restaurants');
			$table->string('Monday');
			$table->string('Tuesday');
			$table->string('Wednesday');
			$table->string('Thursday');
			$table->string('Friday');
			$table->string('Saturday');
			$table->string('Sunday');
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
		Schema::drop('opening_hours');
	}

}
