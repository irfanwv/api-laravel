<?php

namespace App\Prices;

class PriceRepository
{
    protected $model;

    public function __construct (Price $price)
    {
        $this->model = $price;
    }

    public function create ($params)
    {
    	return $this->model->fill($params);
    }
    
    public function search ()
    {
        return $this->model
            ->join('tags', 'tags.id', '=', 'prices.area_id')
            ->select('prices.*')
            ->orderBy('tags.name')
            ->get();
    }

    public function findByCity ($cid)
    {
        return $this->model
            ->whereHas('city', function ($q) use ($cid)
            {
                $q->where('id', $cid); 
            })
            ->firstOrFail();
    }

    public function update ($cid, $params)
    {
        $params['area_id'] = $cid;

        $price = $this->findByCity ($cid);

        $price->delete();

        $new = $this->create($params);

        $this->save($new);

        return $new;
    }

    public function save (Price $price)
    {
        return $price->save();
    }

}
