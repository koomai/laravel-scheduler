<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScheduledTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('scheduler.table'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type');
            $table->string('task');
            $table->string('description', 30)->nullable();
            $table->string('cron');
            $table->string('timezone')->nullable();
            $table->text('environments')->nullable();
            $table->string('queue')->nullable();
            $table->boolean('without_overlapping')->default(0);
            $table->boolean('on_one_server')->default(0);
            $table->boolean('run_in_background')->default(0);
            $table->boolean('in_maintenance_mode')->default(0);
            $table->string('output_path')->nullable();
            $table->boolean('append_output')->default(0);
            $table->string('output_email')->nullable();
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
        Schema::dropIfExists(config('scheduler.table'));
    }
}
