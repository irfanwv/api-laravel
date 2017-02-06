<?php

namespace App\Reminders;

class ReminderRepository
{
    protected $model;

    public function __construct (Reminder $reminder)
    {
        $this->model = $reminder;
    }

    public function find ($id)
    {
    	return (new $this->model)->findOrFail($id);
    }

    public function create ($params)
    {
    	return (new $this->model)->fill($params);
    }

    public function save (Reminder $reminder)
    {
    	return $reminder->save();
    }

    public function search ($include = null, $filter = null, $page = null, $per_page = 10)
    {
        $query = (new $this->model);

        if ($include) {
            if (!is_array($include)) {
                $include = explode(',', $include);
            }
            $query = $query->with($include);
        }

        if ($filter) {
            if (!is_array($filter)) {
                $filter = explode(',', $filter);
            }

            foreach ($filter as $f) {
                $fi = explode(':', $f);
                $fn = trim($fi[0]);
                $fp = trim(isset($fi[1]) ? $fi[1] : null);

                if (!empty($fn) && !empty($fp)) {
                    $query = $query->$fn($fp);
                } else if (!empty($fn)) {
                    $query = $query->$fn();
                }
            }
        }

        if ($page) {
            return $query->paginate ($per_page);
        }

        return $query->get();
    }

    public function createGiftReminder ($params)
    {
        $user = auth()->user();

        $params = array_merge ($params, [
            'user_id' => ($user) ? $user->id : null,
            'first_name' => ($user) ? $user->first_name : $params['first_name'],
            'last_name' => ($user) ? $user->last_name : $params['last_name'],
            'email' => ($user) ? $user->email : $params['email'],
            'reason' => 'gift'
        ]);

        $remind = $this->create ($params);

        $this->save ($remind);

        return $remind;
    }

    public function unsubscribe ($email)
    {
        return (new $this->model())
            ->forGifts()
            ->future()
            ->where('email', $email)
            ->delete();
    }
}
