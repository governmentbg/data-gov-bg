<?php

namespace App\Repositories;

use App\Translator\Translation;
use App\Translator\Translations;
use Illuminate\Database\Eloquent\Collection;

class TranslationsRepository
{
    private $translations = [];
    private $last_translation = null;

    private function _getExisting()
    {
        return array_keys($this->translations);
    }

    public function getLast()
    {
        return $this->last_translation;
    }

    public function get($group_id = null)
    {
        if($group_id)
            return $this->translations[$group_id];
        else
            return $this->translations;
    }

    public function add($type, $group_id = NULL, Collection $data = null)
    {
        $translations = new Translations($group_id,  $data, $type);
        $this->translations[$translations->group_id] = $translations;
        $this->last_translation = $translations;

        return $this;
    }

    public function load($groups, $locale = null)
    {
        // Get the existing groups from the repository
        $existing = $this->_getExisting();

        // Exclude existing groups for the request
        $groups_to_translate = array_filter(array_keys($groups), function($item) use ($existing) {
            return !in_array($item, $existing);
        });

        // Nothing to translate... No need to continue
        if(empty($groups_to_translate))
            return $this;


        $query = Translation::whereIn(
            'group_id',
            $groups_to_translate
        );

        if($locale)
        {
            if(is_array($locale))
                $query->whereIn('locale',$locale);
            else
                $query->where('locale','=',$locale);
        }

        $translations = $query->get();

        foreach($translations->groupBy('group_id')->all() as $group_id=> $translation)
        {
            $this->add($groups[$group_id], $group_id, $translation);
        }

        return $this;
    }

    public function getByGroup($group_id)
    {
        return $this->translations[$group_id];
    }

    public function has($group_id)
    {
        return array_key_exists($group_id, $this->translations);

    }

}
