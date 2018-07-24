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
        $errors = [];
        $class = 'index';
        $loginData = $request->all();

        if ($request->has('username')) {
            $field = filter_var($loginData['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            $validator = \Validator::make($loginData, [
                'username'      => 'required|string|exists:users,'. $field,
                'password'      => 'required|string',
                'remember_me'   => 'nullable|boolean'
            ]);

            if (!$validator->fails()) {
                $request->merge([$field => $loginData['username']]);
                $credentials = $request->only($field, 'password');
                $rememberMe = isset($loginData['remember_me']) ? $loginData['remember_me'] : false;

                if (Auth::attempt($credentials, $rememberMe)) {

                    return redirect('/');
                } else {
                    $errors['password'][0] = 'Wrong password given.';
                }
            } else {
                $errors = $validator->errors()->messages();
            }
        }

        return view('home/login', compact('errors', 'class'));
    }
}
