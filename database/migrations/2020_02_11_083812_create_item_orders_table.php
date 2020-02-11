<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateItemOrdersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('item_orders', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('id_order')->index('FK_item_orders_orders');
			$table->bigInteger('id_menu')->index('FK_item_orders_menus');
			$table->integer('quantity');
			$table->bigInteger('unit_price');
			$table->bigInteger('total_price');
			$table->string('order_message', 110)->nullable();
			$table->boolean('is_cancel')->default(0);
			$table->string('cancel_message', 110)->nullable();
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
		Schema::drop('item_orders');
	}

}
