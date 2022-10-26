<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use App\Models\User;
use App\Models\Student_profile;
use Exception;
use App\Http\Controllers\Api\ApiResponseController;
use App\Http\Controllers\Api\ApiNotificationController;


class AdminController extends Controller
{
    use Notifiable;
    public function delete($id)
    {
        try {

            $response[] = "";
            $user = User::find($id);
            if (!$user) {
                return ApiResponseController::responseNotFound('User Not Found with Id : ' . $id);
            }

            $user->delete();
            $response = ApiResponseController::responseSuccess('User deleted succesfully');
        } catch (\Exception $e) {
            $response = ApiResponseController::responseServerError($e->getMessage());
        }
        return $response;
    }
    public function get_users_for_approval($userType)
    {
        try {
            $response[] = "";
            $users = "";
            if ($userType == 'student') {
                $users = User::join('student_profiles', 'user_id', '=', 'users.id')
                    ->join('addresses', 'addresses.user_id', '=', 'student_profiles.user_id')
                    ->join('parents_details', 'parents_details.user_id', '=', 'student_profiles.user_id')
                    ->select('users.name', 'users.email', 'student_profiles.*', 'addresses.*', 'parents_details.*')
                    ->where('users.user_type', '=', $userType)
                    ->where('users.is_approved', '=', 0)
                    ->get();
            } elseif ($userType == 'teacher') {
                $users = User::join('teacher_profiles', 'user_id', '=', 'users.id')
                    ->join('addresses', 'addresses.user_id', '=', 'teacher_profiles.user_id')
                    ->join('subjects', 'subjects.user_id', '=', 'teacher_profiles.user_id')
                    ->select('users.name', 'users.email', 'teacher_profiles.*', 'addresses.*', 'subjects.*')
                    ->where('users.user_type', '=', $userType)
                    ->where('users.is_approved', '=', 0)
                    ->get();
            } else {
                return ApiResponseController::responseNotFound('Invalid user type');
            }
            if (count($users) > 0) {
                $response = ApiResponseController::responseSuccess(count($users) . ' Users found', $users);
            } else {
                $response = ApiResponseController::responseNotFound('Users Not Found');
            }

            return $response;
        } catch (\Exception $e) {
            return ApiResponseController::responseServerError($e->getMessage());
        }
    }

    public function approve_user($id)
    {
        try {
            $response[] = "";
            $result = User::where('id', $id)
                ->where('is_approved', 0)
                ->update(['is_approved' => 1]);
            if ($result) {

                ApiNotificationController::sendApprovalNotification($id);

                $response = ApiResponseController::responseSuccess('User approved succesfully');
            } else {
                $response = ApiResponseController::responseNotFound('User Not Found or user already approved');
            }
            return $response;
        } catch (\Exception $e) {
            return ApiResponseController::responseServerError($e->getMessage());
        }
    }
    public function approve_all_users()
    {
        try {
            $response[] = "";
            $users = User::where('is_approved', 0)->where('user_type', '!=', 'admin')
                ->get();
            $result = User::where('is_approved', 0)->where('user_type', '!=', 'admin')
                ->update(['is_approved' => 1]);
            if ($result) {
                foreach ($users as $user) {
                    ApiNotificationController::sendApprovalNotification($user->id);
                }
                $response = ApiResponseController::responseSuccess('Users approved succesfully');
            } else {
                $response = ApiResponseController::responseNotFound('Users Not Found or users already approved');
            }
            return $response;
        } catch (\Exception $e) {
            return ApiResponseController::responseServerError($e->getMessage());
        }
    }
    public function assign_teacher(Request $req)
    {
        try {

            $response[] = "";
            $student = User::where('id', $req->student_id)->where('user_type', 'Student')->get();
            $teacher = User::where('id', $req->teacher_id)->where('user_type', 'Teacher')->get();

            if (count($student) > 0) {

                $result = Student_profile::where('user_id', $req->student_id)
                    ->update(['assigned_teacher' => $teacher[0]->name]);

                if ($result) {
                    ApiNotificationController::sendTeacherAssisgnedToStudentNotification($teacher[0]->id, $student[0]->id);

                    $response = ApiResponseController::responseSuccess('Assigned teacher succesfully by name ' .
                        $teacher[0]->name);
                } else {
                    $response = ApiResponseController::responseSuccess('Student has not created profile');
                }
            } else {
                $response = ApiResponseController::responseNotFound('Student not found');
            }

            return $response;
        } catch (Exception $e) {
            return ApiResponseController::responseServerError($e->getMessage());
        }
    }
    public function get_users($usertype)
    {
        try {
            $response[] = "";
            $users = "";
            if ($usertype == 'student') {
                $users = User::where('user_type', 'Student')
                    ->with(['student_profile', 'address', 'parents_detail'])->get();
            } elseif ($usertype == 'teacher') {
                $users = User::where('user_type', 'Teacher')
                    ->with(['teacher_profile', 'address', 'subject'])->get();
            } else {
                return ApiResponseController::responseNotFound('Invalid user type');
            }

            if ($users) {
                $response = ApiResponseController::responseSuccess(
                    count($users) . ' Record found successfully',
                    $users
                );
            } else {
                $response = ApiResponseController::responseNotFound('No record found');
            }
            return $response;
        } catch (\Exception $e) {
            return ApiResponseController::responseServerError($e->getMessage());
        }
    }
    public function sendApprovalNotification($id)
    {
        $apiURL = env('API_URL');
        $client = new \GuzzleHttp\Client();
        return $client->request('POST', $apiURL . '/api/approval_notification/' . $id, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);
    }
    public function sendTeacherAssisgnedToStudentNotification($teacherId, $studentId)
    {
        $apiURL = env('API_URL');
        $client = new \GuzzleHttp\Client();
        return $client->request('POST', $apiURL . '/api/assign_teacher_notification', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'teacher_id' => $teacherId,
                'student_id' => $studentId,
            ]
        ]);
    }
}
