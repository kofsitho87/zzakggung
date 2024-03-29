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
    public function delete(Request $request, History $history)
    {
        if (!$history->delete()) {
            return $this->sendError('FAILED_DELETE_HISTORY');
        }
        return $this->sendResponse([]);
    }

    public function update(Request $request, History $history)
    {
        $credentials = $request->only('status');
        $rules = [
            'status'  => 'required',
        ];
        $validator = Validator::make($credentials, $rules);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_UPDATE_HISTORY', $messages);
        }

        if (!$history->update($credentials)) {
            return $this->sendError('FAILED_UPDATE_HISTORY');
        }

        $data = compact("history");
        return $this->sendResponse($data);
    }
    public function create(Request $request)
    {
        $credentials = $request->only('title', 'desc');
        $rules = [
            'title'  => 'required',
            //'desc'  => 'required',
        ];
        $messages = [
            'title.required'  => '필수값입니다.',
            //'desc.required'  => '필수값입니다.',
        ];
        $validator = Validator::make($credentials, $rules, $messages);
        if ($validator->fails()) {
            $messages = $validator->errors()->messages();
            return $this->sendError('FAILED_CREATE_HISTORY', $messages);
        }

        $history = new History;
        $history->title = $request->title;
        $history->status = "등록";


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
