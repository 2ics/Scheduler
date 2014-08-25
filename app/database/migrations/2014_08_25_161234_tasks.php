<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Tasks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tasks', function($table)
		{
			$table->increments('id');
			$table->integer('project_id')->unsigned()->index();
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->integer('process_id')->unsigned()->index();
			$table->foreign('process_id')->references('id')->on('processes')->onDelete('cascade');
			$table->integer('equipment_id')->unsigned()->index();
			$table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
			$table->double('duration')->nullable();
			$table->string('status')->nullable();
			$table->timestamps();
			$table->softDeletes();

			// We'll need to ensure that MySQL uses the InnoDB engine to
			// support the indexes, other engines aren't affected.
			$table->engine = 'InnoDB';
			$table->index('id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tasks');
	}

}
