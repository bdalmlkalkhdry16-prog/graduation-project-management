<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * إرسال استجابة نجاح
     */
    protected function successResponse($message, $data = null, $status = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * إرسال استجابة خطأ
     */
    protected function errorResponse($message, $status = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * رفع ملف
     */
    protected function uploadFile($file, $path, $oldFile = null)
    {
        // حذف الملف القديم إذا وجد
        if ($oldFile && Storage::exists($oldFile)) {
            Storage::delete($oldFile);
        }

        // رفع الملف الجديد
        $filename = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs($path, $filename, 'public');

        return $filePath;
    }

    /**
     * حذف ملف
     */
    protected function deleteFile($filePath)
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
            return true;
        }
        return false;
    }

    /**
     * تسجيل نشاط
     */
    protected function logActivity($action, $modelType = null, $modelId = null, $oldValues = null, $newValues = null)
    {
        if (auth()->check()) {
            \App\Models\ActivityLog::log(
                auth()->id(),
                $action,
                $modelType,
                $modelId,
                $oldValues,
                $newValues
            );
        }
    }
}
