<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::with('department', 'group', 'schedule')->get();
        return response()->json($employees);
    }

    /**
     * Store a newly created employee in storage.
     *
     * @param  \App\Http\Requests\EmployeeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmployeeRequest $request)
{
    
    $biostarUrl = 'https://10.150.20.173/api/users';

    try {
        $userIdResponse = Http::withOptions(['verify' => false])
            ->withHeaders([
                "bs-session-id" => $request->session_id
            ])
            ->get('https://10.150.20.173/api/users/next_user_id');

        if ($userIdResponse->successful()) {
            $userId = $userIdResponse->json()['User']['user_id'];
        } else {
            return response()->json(['error' => 'Failed to fetch user ID'], $userIdResponse->status());
        }

        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $employee = new Employee();
            $employee->id = $userId;
            $employee->fill($validated);
            $employee->save();

            $biostarUserData = [
                'User' => [
                    'user_id' =>  $userId,
                    'start_datetime' => '2001-01-01T00:00:00.00Z',
                    'expiry_datetime' => '2030-12-31T23:59:00.00Z',
                    'name' => $employee->fullname,
                    'email' => $employee->id . '@gmail.com',
                    'permission' => ['id' => '1'],
                    "login_id" => $employee->id,
                    "password" => "password",
                    "user_group_id" => [
                        "id" => "1"
                    ],
                ]
            ];

            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    "bs-session-id" => $request->session_id
                ])
                ->post($biostarUrl, $biostarUserData);

            if ($response->successful()) {
                DB::commit();
                return response()->json($employee, 201);
            } else {
                DB::rollBack();
                return response()->json(['error' => 'Unexpected response from Biostar API', 'response' => $response->json()], $response->status());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    } catch (RequestException $e) {
        if ($e->getResponse()) {
            // $statusCode = $e->getResponse()->status();
            // $responseBody = $e->getResponse()->json();
            return response()->json([
                'error' => $e->getMessage(),
                'message' => $responseBody['message'] ?? 'Unknown error message',
            ]);
        } else {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

    /**
     * Display the specified employee.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        return response()->json($employee);
    }

    /**
     * Update the specified employee in storage.
     *
     * @param  \App\Http\Requests\EmployeeRequest  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $validated = $request->validated();

        $employee->fill($validated);
        $employee->save();

        return response()->json($employee);
    }


    // public function update(UpdateEmployeeRequest $request, Employee $employee)
    // {
    //     DB::beginTransaction();

    //     try {
    //         $validated = $request->validated();

    //         $employee->fill($validated);
    //         $employee->save();

    //         $biostarUrl = 'https://10.150.20.173/api/users/' . $employee->id;

    //         $biostarUserData = [
    //             'User' => [
    //                 'start_datetime' => $validated['start_datetime'] ?? '2001-01-01T00:00:00.00Z',
    //                 'expiry_datetime' => $validated['expiry_datetime'] ?? '2030-12-31T23:59:00.00Z',
    //                 'name' => $validated['fullname'] ?? 'Unknown',
    //                 'email' => $employee->id . '@gmail.com',
    //                 'permission' => ['id' => '1'],
    //                 'login_id' => $employee->id,
    //                 'password' => 'password',
    //                 'user_group_id' => ['id' => '1'],
    //             ]
    //         ];

    //         $response = Http::withOptions(['verify' => false])
    //             ->withHeaders([
    //                 "bs-session-id" => '6f0999e5dcba4cfbac5bdf318cda7a7a'
    //             ])
    //             ->put($biostarUrl, $biostarUserData);

    //         if ($response->successful()) {
    //             DB::commit();
    //             return response()->json($employee);
    //         } else {
    //             DB::rollBack();
    //             return response()->json(['error' => 'Failed to update user in Biostar API', 'response' => $response->json()], $response->status());
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    /**
     * Remove the specified employee from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return response()->json(null, 204);
    }
}
