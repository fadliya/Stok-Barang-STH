<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_requests', function (Blueprint $table) {
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->string('approver_name')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('incoming_transaction_id')->nullable()->unique()->constrained('incoming_transactions')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('goods_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropConstrainedForeignId('received_by');
            $table->dropConstrainedForeignId('incoming_transaction_id');
            $table->dropColumn(['status', 'approver_name', 'approved_at', 'received_at']);
        });
    }
};
