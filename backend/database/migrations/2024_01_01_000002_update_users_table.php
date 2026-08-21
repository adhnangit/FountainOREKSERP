<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('default_branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('employee_id')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_super_admin')->default(false);
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->string('allowed_ips')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->softDeletes();
        });

        Schema::create('branch_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_user');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'default_branch_id','phone','designation','department','employee_id',
                'avatar','is_active','is_super_admin','two_factor_enabled',
                'two_factor_secret','allowed_ips','last_login_at','last_login_ip','deleted_at'
            ]);
        });
    }
};
