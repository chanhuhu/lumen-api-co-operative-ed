<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;
use App\Models\Skill;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function login(Request $request)
    {
        //find email of user
        $user = User::where('email', $request->email)
            ->first();
        //check password is matched? if matched return success status 200
        if ($user && Hash::check($request->password, $user->password)) {
            return $this->responseRequestSuccess($user);
        }
        //if no matched return error
        return $this->responseRequestError("อีเมล์หรือรหัสผ่านไม่ถูกต้อง");
    }

    public function createSkill(Request $request)
    {
        //find user id
        $user = User::find($request->user_id);
        /*insert all skills example { "skills": ["c++", "haskell",
                                                 "javascript", "python"],
                                      "user_id": 1 }                            */
        $skills = $request->skills;
        if (!empty($skills)) {
            foreach ($skills as $item) {
                //save skill record to db
                $skill = new Skill();
                $skill->name = $item;
                //if save success then add user skill table to
                if ($skill->save()) {
                    $user->skills()->attach($item);
                } else {
                    //return error because server can't insert a record
                    $this->responseRequestError("เซิฟเวอร์มีปัญหา", 500);
                }
            }
            $this->responseRequestSuccess("เพิ่มทักษะสำเร็จ");
        }

    }

    //find the best intern for your company
    public function getBestIntern(Request $request)
    {
        //receive request skills that enterprise want to
        $enterpriseWishSkillLists = $request->enterpriseWishSkillLists;
        $studentSkillLists = User::with('skill')
            ->get();
        /* enterprise wish lists = ["java", "c#"]
           student skill lists = { "john": ["c#", "java"] case1 perfect match
                                  "danny": ["php", "lumen", "javascript", "express"] case2 no match
                                  "jenny": "john": ["c#", "java", "c", "c++"] case3 50% match } */
        $interns = array();
        //loop object key = username and value = student skill list
        foreach ($studentSkillLists as $username => $studentSkillList) {
            //find similar skills
            $similarSkills = $this->findSimilarSkillLists($enterpriseWishSkillLists, $studentSkillLists);
            $interns['student_name'] = $username;
            /*calculate percent of similarity max = 1 example enterprise wish lists = ["java", "c#"] and { "john": ["c#", "java"] }
                                                            length of company wish list and john = 2, 2  in order thus similarity of perfect case
                                                            equals 1  */
            $interns['similarity'] = count($similarSkills) / max(count($studentSkillList), count($enterpriseWishSkillLists));
        }
        return $this->responseRequestSuccess($interns);
    }

    //response company data for selecting
    public function getAllEnterprise(Request $request)
    {
        $enterprises = Enterprise::all();
        return $this->responseRequestSuccess($enterprises);
    }

    //request user_id and images about resume
    public function chooseEnterprise(Request $request, $id)
    {
        $user = User::find($id)
            ->where('state_id', State::$IDEL)
            ->first();
        //validate file ext
        $validator = Validator::make($request->all(), [
            'file_name.*' => 'image|mimes:jpg,jpeg,png,bmp',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->responseRequestError($errors, 400);
        }
        //request->state_id type is reject or approve
        $image = $request->file('file_name');
        list($file_name, $des) = $this->uploadImage($image);
        //set state_id
        $user->state_id = $request->state_id;
        //set url_path
        $user->url_path = $des . $file_name;
        //if user insert success then return response request success
        if ($user->save()) {
            return $this->responseRequestSuccess($user);
        }
        //else error
        return $this->responseRequestError("คุณได้เลือกบริษัทไปแล้ว", 403);
    }

    // for tracking results
    public function getAllUser(Request $request)
    {
        $user = DB::table('users')
            ->join('enterprises', 'users.enterprise_id', '=', 'enterprises.id')
            ->join('sta$imagete_id', '=', 'states.id')
            ->select('users.*', 'states.name', 'enterprises.name')
            ->get();
        return $this->responseRequestSuccess($user);
    }

    private function uploadImage($image)
    {
        $original_filename = $image->getClientOriginalName();
        $original_filename_arr = explode('.', $original_filename);
        $file_ext = end($original_filename_arr);
        $destination_path = './upload/user/';
        $uploadedFileName = 'U-' . time() . '.' . $file_ext;
        if ($image->move($destination_path, $uploadedFileName)) {
            $destination_path = 'http://localhost:8000/upload/user/';
            return [$uploadedFileName, $destination_path];
        }
        return ['State' => 'error', 'error' => 'fail to upload an image (or cv)'];
    }

    private function findSimilarSkillLists($enterpriseWishSkillLists, $studentSkillLists)
    {
        //thing1.filter(item => thing2.includes(item)) Maybe case sensitive
        $similarSkills = array_intersect($enterpriseWishSkillLists, $studentSkillLists);
        return $similarSkills;
    }

    // helper method for return json when status is Ok
    private function responseRequestError($message = 'Bad request', $StateCode = 200)
    {
        return response()->json(['State' => 'error', 'error' => $message], $StateCode)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }

    // helper method for return json when status is not Ok
    private function responseRequestSuccess($ret)
    {
        return response()->json(['State' => 'success', 'data' => $ret], 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    }
}
