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
class PanelClose extends RowAction
{
    /**
     * @return string
     */
	protected $title = '关闭后台';

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
        //$panel_name = $this->row->panel_name;
        $model = MorepanelList::find($this->getKey());
        $model->panel_status = 0;
        $panel_code = $model->panel_code;
        $model->save();
        // 修改配置信息
        //Artisan::call('config:clear');
        //Config::set('app.debug', false);
        //$this->setConfig('admin','multi_app.seller',false);
        config([
            'admin.multi_app.'.lcfirst($panel_code) => false,
        ]);
        //Config::set('admin.multi_app.'.lcfirst($panel_code), false);

        // 永久保存配置信息
        //Artisan::call('config:cache');

        return $this->response()
            ->success('成功关闭: '.$this->getKey())
            ->refresh();
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
		return ['你确认要关闭后台吗?', '关闭后，后台的全部用户不能登陆使用'];
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
