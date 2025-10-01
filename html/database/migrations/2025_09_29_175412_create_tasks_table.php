<?php

use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $attributes = [
        'comments' => '[]',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('creator')->constrained('users');;
            $table->foreignUuid('assignee')->nullable()->constrained('users');;
            $table->foreignUuid('building_id');
            $table->enum('status', TaskStatus::cases());
            $table->string('summary');
            $table->jsonb('comments');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
