<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->string('color_1_oscuro', 7)->nullable()->after('color_7');
            $table->string('color_2_oscuro', 7)->nullable()->after('color_1_oscuro');
            $table->string('color_3_oscuro', 7)->nullable()->after('color_2_oscuro');
            $table->string('color_4_oscuro', 7)->nullable()->after('color_3_oscuro');
            $table->string('color_5_oscuro', 7)->nullable()->after('color_4_oscuro');
            $table->string('color_6_oscuro', 7)->nullable()->after('color_5_oscuro');
            $table->string('color_7_oscuro', 7)->nullable()->after('color_6_oscuro');
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropColumn([
                'color_1_oscuro', 'color_2_oscuro', 'color_3_oscuro',
                'color_4_oscuro', 'color_5_oscuro', 'color_6_oscuro', 'color_7_oscuro',
            ]);
        });
    }
};