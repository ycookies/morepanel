<?php

namespace Dcat\Admin\Morepanel\Http\Controllers;

use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Morepanel\Http\Controllers\Actions\Grid\PanelClose;
use Dcat\Admin\Morepanel\Http\Controllers\Actions\Grid\PanelOpen;
use Dcat\Admin\Morepanel\Http\Controllers\Renderable\PanelUserTable;
use Dcat\Admin\Morepanel\Models\MorepanelList;
use Dcat\Admin\Show;
use Illuminate\Support\Facades\Artisan;

class MorepanelListController extends AdminController {
    /**
     * page index
     */
    public function index(Content $content) {
        return $content
            ->header('多应用后台管理')
            ->description('智能生成，高效管理')
            ->breadcrumb(['text' => '列表', 'uri' => ''])
            ->body($this->grid());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid() {
        return Grid::make(new MorepanelList(), function (Grid $grid) {
            $grid->column('id');
            $grid->column('panel_logo', '后台logo')->image('', '50');
            $grid->column('panel_code', '后台空间名');
            $grid->column('panel_name', '后台中文名');
            $grid->column('panel_user', '后台管理员')
                ->display('管理员列表')
                ->expand(function () {
                    return PanelUserTable::make()->payload(['panel_code' => $this->panel_code]);
                });
            $grid->column('panel_status', '状态')->using([0 => '已禁止', 1 => '启用中']);
            $grid->disableRowSelector();
            $grid->disableFilterButton();
            $grid->setActionClass(Grid\Displayers\Actions::class);
            $grid->actions(function ($actions) {
                // 去掉删除
                $actions->disableDelete();
                // 去掉编辑
                $actions->disableEdit();
                $actions->disableView();
                if (!empty($actions->row->panel_status)) {
                    $actions->append(PanelClose::make());
                } else {
                    $actions->append(PanelOpen::make());
                }
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id) {
        return Show::make($id, new MorepanelList(), function (Show $show) {
            $show->field('id');
            $show->field('panel_code');
            $show->field('panel_name');
            $show->field('panel_logo');
            $show->field('panel_login_bg');
            $show->field('panel_brief');
            $show->field('panel_theme');
            $show->field('panel_menu_style');
            $show->field('panel_status');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form() {
        return Form::make(new MorepanelList(), function (Form $form) {
            $form->display('id');
            $form->text('panel_code', '后台空间名')->help('例:seller')->required();
            $form->text('panel_name', '后台中文名')->help('例:商家端')->required();
            $form->text('panel_brief', '简介说明')->help('例:专注于Laravel项目的极速开发');
            $form->radio('panel_color', '后台风格颜色')->options([
                'default'    => 'default',
                'blue'       => 'blue',
                'blue-light' => 'blue-light',
                'green'      => 'green'
            ])->default('default')->required();
            $form->image('panel_logo', '后台logo')->default('/vendor/dcat-admin/images/logo.png')->required();
            $form->image('panel_login_bg', '后台登陆页背影图')
                ->default('/vendor/dcat-admin/images/login-bg.jpg')
                ->help('尺寸:1280*720')->required();

            $form->switch('panel_status', '是否启用')->default(true);
            $form->saving(function (Form $form) {
                // 判断是否是新增操作
                if ($form->isCreating()) {
                    $count = MorepanelList::where(['panel_code' => $form->panel_code])->count();
                    if ($count) {
                        return $form->response()->error('后台空间名 已经存在.');
                    }
                    $form->panel_code = lcfirst($form->panel_code);
                    $app_name = Ucfirst($form->panel_code);
                    //return $form->response()->error('暂时性关闭创建');
                    $directory = app_path($app_name); // 要检查的目录路径
                    $directory1 = config_path(); // 要检查的目录路径

                    /*// 检查配置后台文件夹有无生成权限
                    if (File::isReadable($directory) && File::isWritable($directory)) {

                    } else {
                        return $form->response()->error('app/'.ucfirst($form->panel_code).' 没有读写权限!');
                    }

                    // 检查配置文件有没有生成权限
                    if (File::isReadable($directory1) && File::isWritable($directory1)) {

                    } else {
                        return $form->response()->error('config/'.lcfirst($form->panel_code).'.php 没有读写权限!');
                    }*/
                }
            });
            $form->saved(function (Form $form, $result) {
                // 判断是否是新增操作
                if ($form->isCreating()) {
                    $newId = $result;
                    if (!$newId) {
                        return $form->response()->error('数据保存失败');
                    }
                    // 生成 后台面板
                    Artisan::call('panel:app', ['name' => ucfirst($form->panel_code)]);
                    //Artisan::call('config:clear');
                }
            });
        });
    }
}
