<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeedbackAnswersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('feedback_answers', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('id_feedback');
			$table->bigInteger('id_user');
			$table->string('answer', 300)->default('N/A');
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
		Schema::drop('feedback_answers');
	}

}
