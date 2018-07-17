<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Contracts\TranslatableInterface;
use App\Translator\Translatable;
use Illuminate\Support\Facades\DB;

class Section extends Model implements TranslatableInterface
{
    use Translatable;

    protected $guarded = ['id', 'name'];

    protected static $translatable = [
        'name' => 'label',
    ];

    public function page()
    {
        return $this->hasMany('App\Page');
    }

    /**
     * Get Section records for listSections API
     *
     * @param array $criteria - filter data for query
     * @return Collection Result from query - Section Records
     */
    public function listSections($criteria = [])
    {
        $sections = DB::table($this->getTable())
                ->join('translations', $this->getTable() .'.name', '=', 'translations.group_id')
                ->select($this->getTable() .'.*', 'translations.locale', 'translations.label' )
                ->where('locale', '=', $criteria['locale']);

        if (isset($criteria['active'])) {
            $sections->where('active', '=', $criteria['active']);
        }

        return $sections->get();
    }

    /**
     * Get Section records for listSubsections API
     *
     * @param array $criteria - filter data for query
     * @return Collection Result from query - Section Records
     */
    public function listSubsections($criteria = [])
    {
        $sections = DB::table($this->getTable())
                ->join('translations', $this->getTable() .'.name', '=', 'translations.group_id')
                ->select($this->getTable() .'.*', 'translations.locale', 'translations.label' )
                ->where('locale', '=', $criteria['locale']);

        if (isset($criteria['parent_id'])) {
            $sections->where('parent_id', '=', $criteria['parent_id']);
        } else {
            $sections->where('parent_id', '<>', 'null');
        }

        if (isset($criteria['active'])) {
            $sections->where('active', '=', $criteria['active']);
        }

        return $sections->get();
    }
}
