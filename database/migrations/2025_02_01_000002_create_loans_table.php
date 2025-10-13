<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
		Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon');
            $table->string('color');
            $table->string('ref')->unique()->index();
            $table->float('amount')->default(0);
            $table->float('amount_now')->default(0);
			$table->float('rate')->default(0);
			$table->date('from')->nullable();
			$table->date('to')->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('needrefresh')->default(false);
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
			$table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
		Schema::dropIfExists('loans');
    }
};
