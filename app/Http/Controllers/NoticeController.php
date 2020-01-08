<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use DB;

use App\Model\User;
use App\Model\Notice;

class NoticeController extends Controller
{
    public function checkAdmin()
    {
        $user = Auth::user();

        if( !$user->is_admin )
        {
            abort(401, 'This action is unauthorized.');
        }
    }

    public function get(Request $request)
    {
        $notice = Notice::find(1);
        return $notice;
    }

    public function createView(Request $request)
    {
        $this->checkAdmin();
        
        $notice = Notice::find(1);
        $data = compact('notice');
        return view('admin_notice', $data);
    }

    public function create(Request $request)
    {
        $this->checkAdmin();

        $credentials = $request->only('content');
        $rules = [
            'content' => "required",
        ];
        $messages = [
            'content.required'  => '필수값입니다.'
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if( $validator->fails() )
        {
            $messages = $validator->errors()->messages();
            return redirect()->back()->withErrors( $validator->errors() );
            //->withInput( $request->except('password') );
        }

        $notice = Notice::find(1);
        $notice->content = $request->content;
        
        if( ! $notice->save() )
        {
            return redirect()->back()->withErrors( 'DB_ERROR: 공지사항 업데이트 실패' );
            //->withInput( $request->except('password') );
        }

        return redirect()->back()->with(['success' => true]);
    }
}
