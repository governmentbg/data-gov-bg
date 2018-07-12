<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Traits\RecordSignature;

class Locale extends Model
{
    use RecordSignature;

    protected $table = 'locale';
    protected $guarded = ['id'];

    public function userSettings()
    {
        return $this->hasMany('App\UserSetting');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation');
    }
}
