<?php

namespace Dcat\Admin\Morepanel\Http\Controllers\Renderable;

use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Widgets\Modal;
use Dcat\Admin\Widgets\Form as WidgetForms;
use Illuminate\Support\Facades\URL;

class PanelUserTable extends LazyRenderable
{
    public $min_panel_code;

    public function grid(): Grid
    {
        $panel_code = ucfirst($this->payload['panel_code']);
        $min_panel_code = lcfirst($this->payload['panel_code']); //小写
        $this->min_panel_code = $min_panel_code;
        $model = "\App\\".ucfirst($panel_code)."\Models\Administrator";
        return Grid::make($model::with(['roles']), function (Grid $grid) use ($min_panel_code) {
            $grid->column('id', 'ID');
            $grid->column('username','用户名');
            $grid->column('name','名称');
            //$grid->column('roles','角色')->pluck('name')->label('primary', 4);
            //$grid->column('status','禁止登陆')->switch();
            $grid->column('created_at','创建时间');
            //$grid->column('updated_at');
            //$grid->quickSearch(['id', 'username', 'name']);

            $grid->paginate(10);
            $grid->setActionClass(Grid\Displayers\Actions::class);
            $grid->enableDialogCreate(); // 打开弹窗创建
            //   快速添加
            /*$grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                $request = Request();
                //$sc_id = $request->get('sc_id');
                //$hangzu_id = $request->get('hangzu_id');
                $create->text('name');
                $create->text('code');
            });*/
            $grid->disableRowSelector();
            $grid->disableToolbar();
            $grid->actions(function ($actions) use ($min_panel_code) {
                // 去掉删除
                $actions->disableDelete();
                // 去掉编辑
                $actions->disableEdit();
                $actions->disableView();
                $form = new WidgetForms();
                $form->confirm('确认现在重置密码？');
                $form->action(admin_url('morepanel/panel-user/resetPassword'));
                $form->html('重置的登陆密码为随机密码');
                $form->hidden('panel_code')->value($min_panel_code);
                $form->text('username', '账号')->value($actions->row->username)->disable()->required();
                $form->hidden('user_id')->value($actions->row->id)->required();
                //$form->disableEditingCheck();
                //$form->disableCreatingCheck();
                //$form->disableViewCheck();

                $modal = Modal::make()
                    ->lg()
                    ->title('重置登陆密码')
                    ->body($form)
                    ->button('<i class="fa fa-key tips" data-title="重置登陆密码"></i>');
                $actions->append($modal);
                $model = "\App\\".$min_panel_code."\Models\Administrator";
                $models = new $model();
                $loginurl = \Illuminate\Support\Facades\URL::signedRoute('panelautologin', ['panel'=>$min_panel_code,'user' => $models->where(['id' => $actions->row->id])->first()], now()->addMinutes(10), true);
                $actions->append('<a class="tips" target="_blank" data-title="登陆后台" href="' . $loginurl . '" > <i class="feather icon-log-in"></i></a>');
            });
        });
    }
}
