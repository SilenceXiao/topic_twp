<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SessionsController extends Controller
{
    //
    public function create(){

        return view('sessions.login');
    }

    //保存登录时的用户会话
    public function store(Request $request){
        $loginmsg = $this->validate($request,[
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        if(Auth::attempt($loginmsg)){
            session()->flash('success', '欢迎回来');
            return redirect()->route('users.show',[Auth::user()]);
            // return view('users.show',['user'=>Auth::user()]);

        }else{
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();
        }
        return;
    }

    public function destory(){

    }
}
