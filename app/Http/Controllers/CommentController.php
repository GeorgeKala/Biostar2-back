<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDayDetail;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function fetchCommentedDetails(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
            $departmentId = $request->input('department_id');
            $forgiveTypeId = $request->input('forgive_type_id');
            $employeeId = $request->input('employee_id');

            $query = EmployeeDayDetail::with(['employee.department', 'dayType', 'forgiveType', 'employee', 'user'])
                ->whereNotNull('comment')
                ->whereBetween('date', [$startDate, $endDate]);

            if ($departmentId) {
                $query->whereHas('employee', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                });
            }

            if ($forgiveTypeId) {
                $query->where('forgive_type_id', $forgiveTypeId);
            }

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            $details = $query->get()->map(function ($detail) {
                return [
                    'employee' => $detail->employee->fullname,
                    'department' => $detail->employee->department->name ?? null,
                    'comment' => $detail->comment,
                    'forgive_type' => $detail->forgiveType->name ?? null,
                    'user' => $detail->user->name ?? null,
                    'created_at' => $detail->created_at->format('Y-m-d H:i:s'),
                    
                ];
            });

            return response()->json([
                'message' => 'Processed Successfully',
                'message_key' => 'SUCCESSFUL',
                'language' => 'en',
                'status_code' => 'SUCCESSFUL',
                'records' => $details
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch details',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function fetchOrders(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
            $departmentId = $request->input('department_id');
            $dayTypeId = $request->input('day_type_id');
            $employeeId = $request->input('employee_id');

            $query = EmployeeDayDetail::with(['employee.department', 'dayType', 'employee'])
                ->whereBetween('date', [$startDate, $endDate]);

            if ($departmentId) {
                $query->whereHas('employee', function ($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                });
            }

            if ($dayTypeId) {
                $query->where('day_type_id', $dayTypeId);
            }

            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }

            $details = $query->get()->map(function ($detail) {
                return [
                    'date' => $detail->date,
                    'employee' => $detail->employee->fullname,
                    'department' => $detail->employee->department->name ?? null,
                    'day_type' => $detail->dayType->name ?? null
                ];
            });

            return response()->json([
                'message' => 'Processed Successfully',
                'message_key' => 'SUCCESSFUL',
                'language' => 'en',
                'status_code' => 'SUCCESSFUL',
                'records' => $details
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
