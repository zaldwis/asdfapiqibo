<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCallingsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('callings', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('id_restaurant')->index('FK_callings_restaurants');
			$table->bigInteger('id_table')->index('FK_callings_tables');
			$table->bigInteger('id_user')->index('FK_callings_users');
			$table->boolean('is_active')->nullable()->default(1);
			$table->bigInteger('calling_type');
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
		Schema::drop('callings');
	}

}
