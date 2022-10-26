<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;

class ApiNotificationController extends Controller
{
    public static function sendApprovalNotification($id)
    {
        try {
            $apiURL = env('API_URL');
            $client = new \GuzzleHttp\Client();
            return $client->request('POST', $apiURL . '/api/approval_notification/' . $id, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
            ]);
        } catch (\Throwable $e) {
            return ApiResponseController::responseServerError($e->getMessage());
        }
    }
    public static function sendTeacherAssisgnedToStudentNotification($teacherId, $studentId)
    {
        try {
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
        } catch (\Throwable $e) {
            return ApiResponseController::responseServerError($e->getMessage());
        }
    }
}
