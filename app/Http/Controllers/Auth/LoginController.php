<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request) {
        $error = [];
        $class = 'index';
        $message = 'Поздравления! Профилът ви беше активиран.
            Вашите данни ще се публикуват като непотвъдени, докaто не ви одобри някой от нашите администратори';
        $loginData = $request->all();

        if ($request->has('username')) {
            $field = filter_var($loginData['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $validator = \Validator::make($loginData, [
                'username'      => 'required|string|exists:users,'. $field,
                'password'      => 'required|string',
                'remember_me'   => 'nullable|boolean'
            ]);

            $validator->after(function ($validator) use ($request) {
                $user = \App\User::where('username', $request->username)->first();

                if (is_null($user)) {
                    $validator->errors()->add('username', __('custom.not_existing_profile'));
                }

                if (isset($user->active) && !$user->active) {
                    $validator->errors()->add('username', __('custom.inactive_profile'));
                }
            });

            if (!$validator->fails()) {
                $request->merge([$field => $loginData['username']]);
                $credentials = $request->only($field, 'password');
                $rememberMe = isset($loginData['remember_me']) ? $loginData['remember_me'] : false;

                if (Auth::attempt($credentials, $rememberMe)) {
                    return redirect('/');
                } else {
                    $error['password'][0] = __('custom.wrong_password');
                }
            } else {
                $error = $validator->errors()->messages();
            }
        }

        return view('home/login', compact('error', 'class', $request->offsetGet('confirmed') ? 'message' : ''));
    }
}
