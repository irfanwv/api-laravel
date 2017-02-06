<?php
namespace App\Users;

use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'customer', 'studio'
    ];

    public function transform(User $user)
    {
       if(isset($_SESSION)){
            $params = [
                'id'         =>  (int) $user->id,
                'first_name' =>  (string) $user->first_name,
                'last_name'  =>  (string) $user->last_name,
                'email'      =>  (string) $user->email,
                'phone'      =>  (string) $user->phone,
                'active'     =>  (bool) $user->active,
                'roles'      =>  $user->tags->map(function($g) { return $g->name; })->toArray(),
                'token'      =>  (string)$_SESSION['activation_code']
            ];
       } else
       {
        $params = [
            'id'         =>  (int) $user->id,
            'first_name' =>  (string) $user->first_name,
            'last_name'  =>  (string) $user->last_name,
            'email'      =>  (string) $user->email,
            'phone'      =>  (string) $user->phone,
            'active'     =>  (bool) $user->active,
            'roles'      =>  $user->tags->map(function($g) { return $g->name; })->toArray()
        ];        
       }

        if ($user->customer) {
            $params['promo'] = (bool) $user->promo;
        }

        return $params;
    }

    public function includeCustomer (User $user)
    {
        return ($user->customer)
            ? $this->item($user->customer, $user->customer->getTransformer())
            : null;
    }

    public function includeStudio (User $user)
    {
        $studio = $user->studio()->withTrashed()->first();
        
        return ($studio)
            ? $this->item($studio, $studio->getTransformer())
            : null;
    }
}
