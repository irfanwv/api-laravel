<?php 

namespace App\Studios;

use DB;

use App\Locations\Location;
use App\Users\User;

use Dingo\Api\Exception\ResourceException;

use App\Tags\Tag;
use App\Tags\TagRepository;

class StudioRepository
{
    protected $model;
    protected $tags;

    public function __construct (Studio $model, TagRepository $tags)
    {
        $this->model = $model;
        $this->tags = $tags;
    }
    
    public function find ($id)
    {
        return (new $this->model)->withTrashed()->findOrFail($id);
    }

    public function findBySlug ($slug, $city = null)
    {
        $sql = "SELECT DISTINCT id, name

        FROM 
            (SELECT users.id as id, studios.name as name,
                to_tsvector(
                    coalesce(studios.name, ' ')  || ' ' ||
                    coalesce(studios.email, ' ')
                ) AS document
            FROM users 
            LEFT JOIN studios on studios.owner_id = users.id
            GROUP BY users.id, studios.name, studios.email) b_search

        WHERE b_search.document @@ to_tsquery(?)
        LIMIT 25;";

        $search = explode('-', $slug);
     
        $search = collect($search)
            ->map(function ($str)
            {
                return "*$str:* & ";
            });

        $search = implode(' ', $search->toArray());

        $result = \DB::select($sql, [trim($search, ' & ')]);

        if (!$city) {
            return $this->find($result[0]->id);
        }

        $ids = collect($result)->map(function ($r)
        {
            return $r->id;
        });

        return (new $this->model)
            ->whereIn ('owner_id', $ids)
            ->byArea ($city)
            ->first ();
    }

    public function create ($input)
    {
        return (new $this->model)->fill ($input);
    }

    public function save (Studio $studio)
    {
        return $studio->save();
    }

    public function update (Studio $studio, $params)
    {
        $studio = $studio->fill ($params);
        
        $this->save ($studio);

        return $studio;
    }

    public function search ($includes = '', $filters = '')
    {
        return (new $this->model)
            ->search ($includes, $filters)
            // ->where ('has_classes', true) // don't show retail only shops in search functions
            // // let the front end determine that
            ->orderBy ('name', 'asc')
            ->get();
    }

    public function searchAndPaginate ($includes = [], $filters = [], $per_page = 20)
    {
        return (new $this->model)
            ->search ($includes, $filters)
            // ->where ('has_classes', true) // don't show retail only shops in search functions
            // // let the front end determine that
            ->orderBy ('name', 'asc')
            ->paginate ($per_page);
    }

    public function useLocation (Studio $studio, Location $location)
    {
        return DB::transaction(function () use ($studio, $location)
        {
            if ($l = $studio->locations->first()) $l->delete();

            $studio->locations()->save($location);

            $studio->location_id = $location->id;
            
            return $this->save($studio);
        });
    }

    public function disable (Studio $studio)
    {
        return $studio->delete();
    }

    public function restore (Studio $studio)
    {
        if (!$studio->area) {
            throw new ResourceException ('The city for that studio is disabled.');
        }

        return $studio->restore();
    }

    public function addTypeById (Studio $studio, $id)
    {
        return $this->addType ($studio, $this->tags->find($id));
    }

    public function addType (Studio $studio, Tag $type)
    {
        if ($studio->types()->where('id', $type->id)->count()) {
            throw new ResourceException ('That studio already uses this type.');
        }

        return $studio->types()->attach($type);
    }

    public function removeType (Studio $studio, Tag $type)
    {
        if (!$studio->types()->where('id', $type->id)->count()) {
            throw new ResourceException ('That studio doesn\'t use this type.');
        }
        
        return $studio->types()->detach($type);
    }
}
