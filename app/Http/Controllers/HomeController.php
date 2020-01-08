<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Validator;
use Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home');
    }

    public function changePwView()
    {
        return view('change_pw');
    }

    public function changePw(Request $request)
    {
        $credentials = $request->only('password', 'new_password');
        $rules = [
            'password'     => 'required|min:6',
            'new_password' => 'required|min:6',
        ];
        $validator = Validator::make($credentials, $rules);
        if( $validator->fails() )
        {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors( $validator->errors() )->withInput();
        }

        $user = Auth::user();

        if( ! Hash::check($request->password, $user->password) )
        {
            return redirect()->back()->withErrors([
                'password' => '현재 비밀번호가 일치하지 않습니다.'
            ])->withInput();
        }

        $user->password = bcrypt($request->new_password);
        
        if( ! $user->save() )
        {
            return redirect()->back()->withErrors([
                'DB' => '비밀번호 업데이트 실패'
            ])->withInput();
        }

        return redirect()->back()->with(['success' => true]);
    }
}
