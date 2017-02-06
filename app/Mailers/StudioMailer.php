<?php 
namespace App\Mailers;

use Log;

use App\Studios\Studio;

class StudioMailer extends Mailer
{
    public function activation (Studio $studio)
    {
    	$user = $studio->owner;

        $subject = 'Please activate your account!';

        $view = 'emails.studio.activation';

        $data = $studio->toArray();

        $data['subject'] = $subject;
        $data['activation_url'] = 'https://' . env('FRONT_DOMAIN') . '/activate?role=studio&code=' . $user->activation_code;
        $data['owner'] = $user->toArray();

        $this->sendTo($user, $subject, $view, $data);
    }

    public function submitApplicationRequest ($params)
    {
        $user = $this->admin;

        $subject = 'A new studio has applied to the program.';

        $view = 'emails.studio.application';

        $data = [
            'market' => $params['market'],
            'studio_name' => $params['studio_name'],
            'first_name' => $params['first_name'],
            'last_name' => $params['last_name'],
            'title' => $params['title'],
            'email' => $params['email'],
            'phone' => $params['phone'],
            'website' => $params['website'],
            'comments' => $params['comments'],
            'subject' => $subject,
        ];

        $this->sendTo ($user, $subject, $view, $data);
    }
}
