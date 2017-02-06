<?php 

namespace App\Users;

use App\Studios\Studio;
use App\Tags\Tag;

use Dingo\Api\Exception\ResourceException;

class UserRepository
{
    protected $umodel;
    protected $tmodel;

    public function __construct (User $umodel, Tag $tmodel)
    {
        $this->umodel = $umodel;
        $this->tmodel = $tmodel;
    }
    
    public function create ($input)
    {
        if (!isset($input['activation_code'])) {
            $input['activation_code'] = str_random(60);
        }

        if (!isset($input['password'])) {
            $input['password'] = bcrypt(str_random(60));
        }

        return (new $this->umodel)->fill ($input);
    }

    public function update (User $user, $params)
    {
        return $this->save ($user->fill($params));
    }

    public function save (User $user)
    {
        return $user->save();
    }

    public function find ($id)
    {
        return (new $this->umodel)->findOrFail ($id);
    }

    public function findByEmail ($email)
    {
        return (new $this->umodel)->where ('email', $email)->firstOrFail();
    }

    public function findByActivation ($code)
    {
        return (new $this->umodel)->where ('activation_code', $code)->firstOrFail();
    }

    public function activateByCode ($code, $password = null)
    {
        $user = $this->findByActivation($code);

        if ($user->isActive()) throw new ResourceException ('You\'re already activated!');

        $user->active = true;
        $user->activation_code = null;
        $user->password = ($password) ? bcrypt($password) : $user->password;

        $this->save($user);

        return $user;
    }

    public function deactivate (User $user)
    {
        $user->active = false;
        $user->activation_code = str_random(60);

        $this->save($user);

        return $user;
    }

    public function changePassword (User $user, $password)
    {
        $user->password = bcrypt($password);

        return $this->save($user);
    }

    public function addStudioTag (User $user)
    {
        return $user->tags()->attach(3);
    }

    public function ownStudio (User $user, Studio $studio)
    {
        return $user->studio()->save($studio);
    }
}
