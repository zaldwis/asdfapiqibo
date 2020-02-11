<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFriendRequestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('friend_requests', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('id_sender');
			$table->bigInteger('id_receiver');
			$table->boolean('is_accept')->default(0);
			$table->boolean('is_reject')->default(0);
			$table->boolean('is_delete')->default(0);
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
		Schema::drop('friend_requests');
	}

}
