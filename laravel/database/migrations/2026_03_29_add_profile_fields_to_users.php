<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Basic Profile
            $table->string('position')->nullable()->after('department');
            $table->string('contact_number')->nullable()->after('position');
            $table->string('shift_schedule')->nullable()->after('contact_number');

            // Work Info
            $table->date('hire_date')->nullable()->after('shift_schedule');
            $table->enum('work_location', ['office', 'wfh', 'hybrid'])->default('office')->after('hire_date');

            // Hierarchy
            $table->string('manager_name')->nullable()->after('work_location');

            // Emergency Contact (Compliance/Audit)
            $table->string('emergency_contact_name')->nullable()->after('manager_name');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'position',
                'contact_number',
                'shift_schedule',
                'hire_date',
                'work_location',
                'manager_name',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
            ]);
        });
    }
};
