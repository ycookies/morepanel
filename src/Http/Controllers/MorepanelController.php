<?php

namespace Dcat\Admin\Morepanel\Http\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Illuminate\Routing\Controller;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Illuminate\Http\Request;
use Dcat\Admin\Widgets\Form as WidgetForm;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;

class MorepanelController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->title('多应用后台生成器')
            ->description('一键生成独立应用后台,可无限生成')
            ->body($this->pageMain());
    }

    public function pageMain(){

        return '123';
    }

    // 重置面板管理员密码
    public function resetPassword(Request $request){
        $panel_code = $request->get('panel_code');
        $user_id = $request->get('user_id');
        if(empty($user_id)){
            return (new WidgetForm())->response()->error('用户不存在,请检查');
        }
        $model = "\App\\".ucfirst($panel_code)."\Models\Administrator";
        $newpsd = Str::random(8);
        $user = $model::find($user_id);
        $user->password = Hash::make($newpsd);
        $user->setRememberToken(Str::random(60));
        $user->save();

        return (new WidgetForm())->response()->alert(true)->success($newpsd)->detail('重置的登陆密码');
    }

    // 自动登陆
    public function panelautologin(Request $request,$panel,$user){
        $model = "\App\\".ucfirst($panel)."\Models\Administrator";
        $status = Config::get('admin.multi_app.'.$panel);
        if($status === true){
            Auth::guard($panel)->check();
            $models = new $model();
            Auth::guard($panel)->login($models->where(['id'=>$user])->first());
            return redirect('/'.$panel);
        }
        echo "<div style='text-align: center;margin-top: 100px;font-size: 22px'> 后台未启用 或 已禁止使用</div>";
        exit;

    }
}