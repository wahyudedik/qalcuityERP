<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('custom_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Display
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('icon_bg')->default('bg-blue-500/20');
            $table->string('icon_color')->default('text-blue-400');
            $table->tinyInteger('cols')->default(1); // 1 | 2 | 4

            // Data source
            // metric_type: count | sum | avg | raw_sql | static
            $table->string('metric_type')->default('count');
            // model_class: e.g. App\Models\SalesOrder (used for count/sum/avg)
            $table->string('model_class')->nullable();
            // metric_column: column to sum/avg, e.g. total
            $table->string('metric_column')->nullable();
            // where_json: JSON array of [column, operator, value] triples for filtering
            $table->json('where_conditions')->nullable();
            // date_scope: today | this_month | this_year | all_time
            $table->string('date_scope')->default('this_month');
            $table->string('date_column')->default('created_at');
            // prefix/suffix for formatting the value (e.g. "Rp " or " pcs")
            $table->string('value_prefix')->nullable();
            $table->string('value_suffix')->nullable();
            // format: number | currency | percent
            $table->string('value_format')->default('number');

            // Access
            // visible_to_roles: JSON array of roles that can add this widget, null = all roles
            $table->json('visible_to_roles')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_dashboard_widgets');
    }
};
