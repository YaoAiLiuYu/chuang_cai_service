<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function FileUpload(Request $request)
    {
        $file = $request->file('file');
        $ext = $file->getClientOriginalExtension();
        $filename = md5(uniqid()) . "." . $ext;
        $path = "images/" . date("Ymd");
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $data['path'] = $file->storeAs($path, $filename);
        return $this->responseData($data);
    }
}
