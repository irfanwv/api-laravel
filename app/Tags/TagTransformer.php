<?php
namespace App\Tags;

use League\Fractal\TransformerAbstract;

use App\Prices\PriceTransformer;
use App\Studios\StudioTransformer;
use App\Users\UserTransformer;

class TagTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'users', 'studios', 'children', 'parent', 'price'
    ];

    public function transform(Tag $tag)
    {
        $params = [
            'id'        =>  (int)   $tag->id,
            'parent_id' =>  (int)   $tag->parent_id,
            'name'      =>  (string) $tag->name,
            'type'      =>  (string) $tag->type
        ];

        return $params;
    }

    public function includeUsers (Tag $tag) 
    {
        return $this->collection($tag->users, new UserTransformer);
    }

    public function includeStudios (Tag $tag) 
    {
        return $this->collection($tag->studios, new StudioTransformer);
    }

    public function includeChildren (Tag $tag)
    {
        return $this->collection($tag->children->sortBy('name'), new TagTransformer);
    }

    public function includeParent (Tag $tag)
    {
        if (!$tag->parent) return null;

        return $this->item($tag->parent, new TagTransformer);
    }

    public function includePrice (Tag $tag)
    {
        return ($tag->price) ? $this->item($tag->price, new PriceTransformer) : null;
    }
}
