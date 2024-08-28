<?php

namespace Dcat\Admin\Morepanel\Http\Controllers\Actions\Grid;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Dcat\Admin\Morepanel\Models\MorepanelList;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
class PanelOpen extends RowAction
{
    /**
     * @return string
     */
	protected $title = '启用后台';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        // dump($this->getKey());
        $model = MorepanelList::find($this->getKey());
        $model->panel_status = 1;
        $panel_code = $model->panel_code;
        $model->save();
        //$this->setConfig('admin','multi_app.seller',true);
        config([
            'admin.multi_app.'.lcfirst($panel_code) => true,
        ]);
        Artisan::call('config:cache');
        /*Artisan::call('config:clear');
        // 修改配置信息
        Config::set('admin.multi_app.'.lcfirst($panel_code), true);
        // 永久保存配置信息
        Artisan::call('config:cache');*/
        //MorepanelList::where(['id'=>$this->getKey()])->update(['panel_status'=>1]);

        return $this->response()->success('启用成功:')->refresh();
    }

    public function setConfig($configFile,$name, $value)
    {
        $config = Config::get($configFile);
        $new_name = explode('.',$name);
        if(count($new_name) == 1){
            $config[$new_name[0]][$name] = $value;
        }
        if(count($new_name) == 2){
            $config[$new_name[0]][$new_name[1]] = $value;
        }
        if(count($new_name) == 3){
            $config[$new_name[0]][$new_name[1]][$new_name[2]] = $value;
        }
        $path = base_path()  . DIRECTORY_SEPARATOR . 'config'.DIRECTORY_SEPARATOR.$configFile.'.php';
        file_put_contents($path, "<?php \n return ".var_export($config, true)  . ";");

    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
        return ['你确认要启用后台吗?', '启用后，可正常登陆使用'];
	}

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}
