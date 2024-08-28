<?php

namespace Dcat\Admin\Morepanel\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class MorepanelList extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'morepanel_list';
    //protected $fillable = ['panel_status'];
    protected $guarded = [];
}
