<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeedbackOptionalsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('feedback_optionals', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('id_feedback');
			$table->string('option_A', 300);
			$table->string('option_B', 300);
			$table->string('option_C', 300);
			$table->string('option_D', 300);
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
		Schema::drop('feedback_optionals');
	}

}
