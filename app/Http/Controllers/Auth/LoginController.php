<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Api\RightController;
use App\Http\Controllers\Api\UserController;
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
            $validator = \Validator::make($loginData, [
                'username'      => 'required|string',
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
                $request->merge(['username' => $loginData['username']]);
                $credentials = $request->only('username', 'password');
                $rememberMe = isset($loginData['remember_me']) ? $loginData['remember_me'] : false;

                if (Auth::attempt($credentials, $rememberMe)) {
                    $user = \App\User::where('username', $request->username)->first();
                    $rq = Request::create('/api/getUserRoles', 'POST', ['id' => $user->id]);
                    $api = new UserController($rq);
                    $result = $api->getUserRoles($rq)->getData();

                    if ($result->success) {
                        Session::push('roles', $result->data->roles);
                    }

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
