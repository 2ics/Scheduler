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
			$table->integer('equipment_id')->unsigned()->index();
			$table->foreign('equipment_id')->references('id')->on('equipment')->onDelete('cascade');
			$table->integer('customer_id')->unsigned()->index();
			$table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
			$table->integer('user_id')->unsigned()->index();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->string('docket')->nullable();
			$table->string('description')->nullable();
			$table->string('press')->nullable();
			$table->string('sheets')->nullable();
			$table->string('due_date')->nullable();
			$table->string('notes')->nullable();
			$table->string('duration')->nullable();
			$table->string('status')->nullable();
			$table->string('stock')->nullable();
			$table->string('start_date')->nullable();
			$table->string('end_date')->nullable();
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
