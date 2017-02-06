<?php
namespace App\Studios;

use League\Fractal\TransformerAbstract;

use App\Tags\TagTransformer;

class StudioTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'owner', 'location', 'tags' , 'types', 'city'
    ];

    public function transform (Studio $studio)
    {
        $params = [
            'id'        =>  (int) $studio->owner_id,
            'name'      =>  (string) $studio->name,
            'slug'      =>  (string) str_slug($studio->name),
            'email'     =>  (string) $studio->email,
            'phone'     =>  (string) $studio->phone,
            'website'   =>  (string) $studio->website,
            'description' => (string) $studio->description,
            'retailer'    =>  (bool) $studio->is_retailer,
            'studio'    =>  (bool) $studio->has_classes,
            'deleted'   =>  (bool) $studio->deleted_at,
            'typesList' =>  implode(', ', $studio->types
                                ->map(function ($type) { return $type->name; })
                                ->toArray())
        ];

        $area = $studio->area()->withTrashed()->first();

        if ($area->parent) {
            $params['area'] = $area->parent->name;
            $params['neighbourhood'] = $area->name;
        } else {
            $params['area'] = $area->name;
            $params['neighbourhood'] = $area->name;
        }

        return $params;
    }

    /**
     * Include Owner
     *
     * @return League\Fractal\ItemResource
     */
    public function includeOwner(Studio $studio)
    {
        return $this->item($studio->owner, $studio->owner->getTransformer());
    }

    /**
     * Include just the first location
     *
     * @return League\Fractal\ItemResource
     */
    public function includeLocation(Studio $studio)
    {
        if ($location = $studio->location)
            return $this->item($location, $location->getTransformer());
        return null;
    }

    public function includeCity(Studio $studio)
    {
        $tag = $studio->area()->first();
        
        return $this->item($tag, new TagTransformer);
    }

    /**
     * Include the tags they're in (areas)
     *
     * @return League\Fractal\ItemResource
     */
    public function includeTags(Studio $studio)
    {
        $tag = $studio->tags;
        
        return $this->collection($tag, new TagTransformer);
    }

    /**
     * Include the tags they're in (areas)
     *
     * @return League\Fractal\ItemResource
     */
    public function includeTypes(Studio $studio)
    {
        $tag = $studio->types;
        
        return $this->collection($tag, new TagTransformer);
    }
}
