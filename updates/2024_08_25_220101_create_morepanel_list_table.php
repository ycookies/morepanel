<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMorepanelListTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('morepanel_list', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('panel_code')->default('')->unique()->comment('后台英文名');
            $table->string('panel_name')->default('')->comment('后台中文名');
            $table->string('panel_logo')->nullable()->comment('后台logo');
            $table->string('panel_login_bg')->nullable()->comment('后台登陆页背景图');
            $table->text('panel_brief')->nullable()->comment('简介');
            $table->string('panel_theme')->nullable()->comment('后台面板主题风格');
            $table->string('panel_color')->default('default')->comment('后台面板主题风格');
            $table->string('panel_menu_style')->nullable()->comment('后台菜单样式');
            $table->integer('panel_status')->nullable()->comment('后台状态');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('morepanel_list');
    }
}
