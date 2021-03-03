<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    //

    public function __construct()
    {
        //中间键
        $this->middleware('auth',[
            'except' => ['show','create','store','index','confirmEmail'], #指定不用过滤
            // 'only' => ['show','create','store'] # 指定过滤
        ]);

        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

    public function create(){
        return view('users.signup');
    }

    public function show(User $user){
        return view('users.show',compact('user'));
    }

    //用户注册
    public function store(Request $request){
        $this->validate($request,[
            'name' => "required|unique:users|max:255",
            "email" => "required|email|unique:users|max:255",
            "password" => "required|confirmed|min:4"
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        
        $this->sendEmail($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收');
        // Auth::login($user);
        // return view('users.show',['user' => $user]);
        // return redirect()->route('users.show', [$user]);
        return redirect('/');
    }


    public function edit(User $user){
        $this->authorize('update',$user);
        // return view('users.edit',compact('user'));
        return view('users.edit',['user' => $user]);
    }

    //编辑
    public function update(User $user, Request $request){
        $this->authorize('update',$user);
        
        $this->validate($request,[
            'name' => "required|unique:users|max:50",
            "password" => "nullable|confirmed|min:4"
        ]);
        $data = [];
        $data['name'] = $request->name;
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        session()->flash('success','更新成功');
        return redirect()->route('users.show',[$user]);
    }

    //列表
    public function index(){
        $users = User::paginate(5);
        return view('users.index',['users'=>$users]);
    }


    public function destroy(User $user){
        $this->authorize('destroy', $user); //授权
        $user->delete();
        session()->flash('success','删除成功');
        return back();
    }

    //用户激活邮件验证
    public function confirmEmail($userId,$token){
        $user = User::findOrFail($userId);
        if($user->active_token == $token){
            $user->active = true;
            $user->active_token = null;
            $user->save();
            Auth::login($user);

            session()->flash('success','激活成功,并登录成功');
            return redirect()->route('users.show',[$user]);

        }else{
            session()->flash('warning','激活失败,请再次尝试');
            return redirect()->back();
        }
        // $user = User::where('active_token',$token)->firstOrFail();
        
    }

    //用户注册发送邮件
    protected function sendEmail($user){
        $view = 'emails.email';
        $data = compact('user');
        $from = 'wl@example.com';
        $name = 'wl';
        $to = $user->email;
        $subject = "感谢注册 TopicTwo 应用！请确认你的邮箱。";
        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }
}
