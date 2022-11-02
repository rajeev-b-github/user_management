<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\ApiResponseController;

class ApiNotificationController extends Controller
{

    public function sendApprovalNotification($id)
    {
        return $this->sendNotification('/api/approval_notification/' . $id);
    }
    public function sendTeacherAssisgnedToStudentNotification($teacherId, $studentId)
    {
        return $this->sendNotification('/api/assign_teacher_notification', $studentId, $teacherId);
    }

    public function sendNotification($notifiactionType, $studentId = 0, $teacherId = 0)
    {
        try {
            $apiURL = config('services.notification.url');
            $client = new \GuzzleHttp\Client();
            return $client->request('POST', $apiURL . $notifiactionType, [
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
