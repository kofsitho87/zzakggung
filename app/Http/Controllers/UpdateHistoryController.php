<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use DB;
use Artisan;
use Auth;

use App\Model\History;

//use Api\BaseController

class UpdateHistoryController extends Api\BaseController
{
    public function create(Request $request)
    {
        $credentials = $request->only('title', 'desc');
        $rules = [
            'title'  => 'required',
            'desc'  => 'required',
        ];
        $messages = [
            'title.required'  => '필수값입니다.',
            'desc.required'  => '필수값입니다.',
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_CREATE_HISTORY', $messages);
        }

        $history = new History;
        $history->title = $request->title;
        $history->desc = $request->desc;

        if (!$history->save()) {
            return $this->sendError('FAILED_CREATE_HISTORY', "");
        }
        $data = compact("history");
        return $this->sendResponse($data);
    }

    public function getItems(Request $request)
    {
        $items = History::orderByDesc('id')->paginate(100);
        $data = compact('items');
        return $this->sendResponse($data);
    }
}
