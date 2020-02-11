<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMenusTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('menus', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('id_restaurant')->index('FK_menus_restaurants');
			$table->bigInteger('id_menu_category')->index('FK_menus_menu_categories');
			$table->string('name');
			$table->string('description');
			$table->string('image');
			$table->bigInteger('price');
			$table->boolean('is_available')->nullable();
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
		Schema::drop('menus');
	}

}
