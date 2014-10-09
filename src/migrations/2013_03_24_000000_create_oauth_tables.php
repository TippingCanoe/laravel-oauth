<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateOauthTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {


		Schema::create('oauth_consumer', function(Blueprint $table) {

			$table
				->increments('id')
				->unsigned()
			;

			$table
				->string('name')
				->unique('U_consumer_name')
			;

			$table
				->boolean('enabled')
				->default(false)
			;

			$table
				->string('key')
				->unique('U_consumer_key')
			;

			$table
				->string('secret')
				->unique('U_consumer_secret')
			;

			$table->timestamps();

		});

		Schema::create('oauth_access_token', function(Blueprint $table) {

			$table
				->increments('id')
				->unsigned()
			;

			$table
				->integer('consumer_id')
				->unsigned()
			;

			$table
				->string('oauthable_type')
			;

			$table
				->integer('oauthable_id')
				->unsigned()
			;

			$table
				->string('key')
				->unique('U_access_token_key')
			;

			$table
				->string('secret')
				->unique('U_access_token_secret')
			;

			$table->timestamps();

		});

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('oauth_consumer');
		Schema::dropIfExists('oauth_access_token');
	}

}