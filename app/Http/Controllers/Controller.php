<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function login(Request $request)
    {
        $student = Student::where('email', $request->email)
            ->where('status_id', Status::$INACTIVE)
            ->first();
        if ($student && Hash::check($request->password, $student->password)) {
            return $this->responseRequestSuccess($student);
        }
        return $this->responseRequestError("อีเมล์หรือรหัสผ่านไม่ถูกต้อง");
    }

    public function registerCoOperativeEd(Request $request)
    {
        //choose enterprise and send cv enterprise
    }

    public function sendCV(Request $request)
    {
        //
    }

    public function getState(Request $request)
    {
        //
    }

    protected function responseRequestError($message = 'Bad request', $statusCode = 200)
    {
        return response()->json(['status' => 'error', 'error' => $message], $statusCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    protected function responseRequestSuccess($ret)
    {
        return response()->json(['status' => 'success', 'data' => $ret], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
}
