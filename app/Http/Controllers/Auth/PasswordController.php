<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Http\Requests\Auth\PasswordResetRequest;
use App\Http\Requests\Auth\PasswordEmailRequest;

use App\Users\UserRepository;
use App\Passports\PassportRepository;
use App\Mailers\UserMailer;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    protected $users;

    public function __construct (UserRepository $users)
    {
        $this->users = $users;
    }

    public function email (PasswordEmailRequest $request)
    {
        $response = Password::sendResetLink($request->only('email'), function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return $this->response->noContent();

            case Password::INVALID_USER:
                return $this->response->error('No account associated with that address.', 422);
        }
    }

    public function reset (PasswordResetRequest $request)
    {
        $credentials = $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );

        $credentials['email'] = str_replace(' ', '+', $credentials['email']);

        $response = Password::reset($credentials, function ($user, $password) {
            $this->resetPassword ($user, $password);
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                return $this->response->noContent();

            default:
                return $this->response->error('There was a problem with your request', 422);
        }
    }

    public function update (PasswordResetRequest $request, $uid = null)
    {
        if ($uid) $user = $this->users->find($uid);

        else $user = $this->auth->user();

        $this->users->changePassword ($user, $request->get('password'));

        return $this->response->noContent();
    }

    public function username (Request $request, UserMailer $mailer, PassportRepository $passports)
    {
        if (!$passport = $passports->findByNumber($request->get('number'))) {
            return $this->response->error('That passport has no login.', 422);
        }

        if (!$passport->owner) {
            return $this->response->error('That passport has no login.', 422);
        }

        $mailer->sendUsernameReminder ($passport->owner->user);

        return $this->response->noContent();
    }
}
