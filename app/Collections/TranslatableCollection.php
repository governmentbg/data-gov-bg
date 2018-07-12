<?php

namespace App\Collections;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Support\Arrayable;
use App\Translator\Translations;
use App\Repositories\TranslationsRepository;

class TranslatableCollection extends Collection
{

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerializeWithTranslations()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerializeWithTranslations();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toTranslatedJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toTranslatedArray();
            } else {
                return $value;
            }
        }, $this->items);
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toTranslatedJson($options = 0)
    {
        return json_encode($this->jsonSerializeWithTranslations(), $options);
    }

    public function toTranslatedArray($all = false)
    {
        return array_map(function ($value) use($all) {
            if($value instanceof \App\Contracts\TranslatableInterface) {
                return $value->toTranslatedArray($all);
            }
            elseif($value instanceof Arrayable) {
                return $value->toArray();
            }
            else {
            return $value;
            }
        }, $this->items);
    }

    /**
     * Load all translations corresponding to a model inside this collection
     * @method loadTranslations
     * @return [type]           [description]
     */
    public function loadTranslations()
    {
        $values_needing_translation = [];

        foreach($this->items as $item)
        {
            $values_needing_translation = array_replace(
                $values_needing_translation,
                $item->get_keys_with_types()
            );
        }

        $repo = app(TranslationsRepository::class);
        $repo->load($values_needing_translation);

        return $this;
    }
}
