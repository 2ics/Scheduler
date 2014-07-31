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
			$table->string('docket')->nullable();
			$table->string('customer')->nullable();
			$table->string('description')->nullable();
			$table->string('press')->nullable();
			$table->string('sheets')->nullable();
			$table->string('deletion_date')->nullable();
			$table->string('rep')->nullable();
			$table->string('notes')->nullable();
			$table->string('duration')->nullable();
			$table->string('colour')->nullable();
			$table->string('status')->nullable();
			$table->string('stock')->nullable();
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