<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCheckinsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('checkins', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('id_restaurant')->index('FK_checkins_restaurants');
			$table->bigInteger('id_table')->index('FK_checkins_tables');
			$table->bigInteger('id_user')->index('FK_checkins_users');
			$table->dateTime('checkin_datetime')->nullable();
			$table->dateTime('checkout_datetime')->nullable();
			$table->boolean('is_checkin')->nullable()->default(1);
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
		Schema::drop('checkins');
	}

}
