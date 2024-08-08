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
                    'id' => $detail->id,
                    'employee' => $detail->employee->fullname,
                    'department' => $detail->employee->department->name ?? null,
                    'comment' => $detail->comment,
                    'forgive_type' => $detail->forgiveType->name ?? null,
                    'user' => $detail->user->name ?? null,
                    'created_at' => $detail->created_at->format('Y-m-d H:i:s'),

                ];
            });

            return response()->json($details);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch details',
                'error' => $e->getMessage(),
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
                    'day_type' => $detail->dayType->name ?? null,
                ];
            });

            return response()->json($details);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAnalyzedComments(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $departmentId = $request->input('department_id');
        $forgiveTypeId = $request->input('forgive_type_id');
        $employeeId = $request->input('employee_id');
        $forgive = $request->input('forgive');

        $query = EmployeeDayDetail::query()
            ->join('employees', 'employee_day_details.employee_id', '=', 'employees.id')
            ->join('forgive_types', 'employee_day_details.forgive_type_id', '=', 'forgive_types.id')
            ->whereNull('employee_day_details.day_type_id')
            ->whereNotNull('employee_day_details.comment')
            ->whereBetween('employee_day_details.date', [$startDate, $endDate])
            ->select('employee_day_details.*', 'employees.fullname', 'forgive_types.forgive');

        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }

        if ($forgiveTypeId) {
            $query->where('employee_day_details.forgive_type_id', $forgiveTypeId);
        }

        if ($employeeId) {
            $query->where('employee_day_details.employee_id', $employeeId);
        }

        if ($forgive !== null) {
            $query->where('forgive_types.forgive', $forgive);
        }

        $dayDetails = $query->get();

        $formattedDetails = $dayDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                'employee_fullname' => $detail->fullname,
                'date' => $detail->date,
                'comment' => $detail->comment,
                'created_at' => $detail->created_at,
                'updated_at' => $detail->updated_at,
                'day_type_id' => $detail->day_type_id,
                'forgive_type_id' => $detail->forgive_type_id,
                'user_id' => $detail->user_id,
                'final_penalized_time' => $detail->final_penalized_time,
                'comment_datetime' => $detail->comment_datetime,
                'forgive' => $detail->forgive,
            ];
        });

        return response()->json($formattedDetails);
    }
}
