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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description');

            $table->foreignId('user_id')->constrained('users'); // Usuario que crea
            $table->foreignId('assigned_to')->nullable()->constrained('users'); // Usuario asignado

            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('priority_id')->constrained('priorities');
            $table->foreignId('status_id')->constrained('ticket_statuses');
            $table->foreignId('sla_id')->nullable()->constrained('slas');

            $table->timestamp('due_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('assigned_to');
            $table->index('category_id');
            $table->index('priority_id');
            $table->index('status_id');
            $table->index('sla_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
