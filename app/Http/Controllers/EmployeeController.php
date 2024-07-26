<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): JsonResponse
    {
        $employees = Employee::with('department', 'group', 'schedule', 'holidays', 'user')
            ->whereNull('expiry_datetime')
            ->get();

        return response()->json($employees);
    }


    public function archivedEmployees(): JsonResponse
    {
        $archivedEmployees = Employee::with('department', 'group', 'schedule', 'holidays', 'user')
            ->whereNotNull('expiry_datetime')
            ->get();

        return response()->json($archivedEmployees);
    }

    /**
     * Store a newly created employee in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(EmployeeRequest $request): JsonResponse
    {

        $biostarUrl = 'https://10.150.20.173/api/users';

        try {
            $userIdResponse = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $request->session_id,
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

                $employee = new Employee;
                $employee->id = $userId;
                $employee->fill($validated);
                $employee->save();

                if (isset($validated['holidays'])) {
                    $employee->holidays()->attach($validated['holidays']);
                }

                $department = $employee->department;
                if ($department && $department->buildings) {
                    $accessGroups = $department->buildings->pluck('access_group')->unique();
                    $formattedAccessGroups = $accessGroups->map(function ($groupId) {
                        return ['id' => $groupId];
                    })->values();
                } else {
                    $formattedAccessGroups = [];
                }

                $biostarUserData = [
                    'User' => [
                        'user_id' => $userId,
                        'start_datetime' => '2001-01-01T00:00:00.00Z',
                        'expiry_datetime' => '2030-12-31T23:59:00.00Z',
                        'name' => $employee->fullname,
                        'email' => $employee->id.'@gmail.com',
                        'permission' => ['id' => '1'],
                        'login_id' => $employee->id,
                        'password' => 'password',
                        'user_group_id' => [
                            'id' => '1',
                        ],
                        'access_groups' => $formattedAccessGroups,
                    ],
                ];

                $response = Http::withOptions(['verify' => false])
                    ->withHeaders([
                        'bs-session-id' => $request->session_id,
                    ])
                    ->post($biostarUrl, $biostarUserData);

                if ($response->successful()) {
                    $card_id = $this->makeCard($request->card_number, $request->session_id);
                    $final_result = $this->updateUserCards($userId, $card_id, $request->session_id);
                    DB::commit();

                    return response()->json($final_result, 201);
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
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {

        DB::beginTransaction();

        try {
            $validated = $request->validated();
            $employee->fill($validated);
            $employee->save();

            if (isset($validated['holidays'])) {
                $employee->holidays()->sync($validated['holidays']);
            } else {
                $employee->holidays()->detach();
            }

            $biostarUrl = 'https://10.150.20.173/api/users/'.$employee->id;

            $biostarUserData = [
                'User' => [
                    'name' => $validated['fullname'],
                ],
            ];
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $request->session_id,
                ])
                ->put($biostarUrl, $biostarUserData);

            if ($response->successful()) {

                DB::commit();

                return response()->json($employee);
            } else {
                DB::rollBack();

                return response()->json(['error' => 'Failed to update user in Biostar API', 'response' => $response->json()], $response->status());
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified employee from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee, Request $request): JsonResponse
    {
        $biostarUrl = 'https://10.150.20.173/api/users/'.$employee->id;
        try {
            DB::beginTransaction();

            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $request->header()['bs-session-id'][0],
                ])
                ->delete($biostarUrl);

            if ($response->successful()) {
                $employee->delete();
                DB::commit();

                return response()->json(null, 204);
            } else {
                DB::rollBack();

                return response()->json(['error' => 'Failed to delete user in Biostar API', 'response' => $response->json()], $response->status());
            }
        } catch (RequestException $e) {
            DB::rollBack();
            if ($e->getResponse()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'message' => $e->getResponse(),
                ]);
            } else {
                return response()->json([
                    'error' => $e->getMessage(),
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function makeCard($iterIDVal, $bsSessionId)
    {
        $url = 'https://10.150.20.173/api/cards';

        $payload = [
            'CardCollection' => [
                'rows' => [
                    [
                        'card_id' => $iterIDVal,
                        'card_type' => [
                            'id' => '1',
                            'name' => '',
                            'type' => '10',

                        ],
                        'wiegand_format_id' => [
                            'id' => '0',
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $bsSessionId,
                ])
                ->post($url, $payload);

            if ($response->successful()) {

                return $response->json()['CardCollection']['rows'][0]['id'];
            } else {
                throw new \Exception('Failed to make card request: '.$response->status());
            }
        } catch (\Exception $e) {
            throw new \Exception('Error making card request: '.$e->getMessage());
        }
    }

    private function updateUserCards($userId, $iterIDVal, $bsSessionId)
    {

        $url = "/https://10.150.20.173/api/users/{$userId}";

        $payload = [
            'User' => [
                'cards' => [
                    [
                        'id' => $iterIDVal,
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $bsSessionId,
                    'Content-Type' => 'application/json',
                ])
                ->put($url, $payload);

            if ($response->successful()) {
                return $response->json();
            } else {
                throw new \Exception('Failed to update user cards: '.$response->status());
            }
        } catch (\Exception $e) {
            throw new \Exception('Error updating user cards: '.$e->getMessage());
        }
    }

    public function searchEvents(Request $request)
    {
        $url = 'https://10.150.20.173/api/events/search';

        $startOfDay = Carbon::now()->startOfDay()->format('Y-m-d\TH:i:s.000\Z');
        $endOfDay = Carbon::now()->endOfDay()->format('Y-m-d\TH:i:s.000\Z');

        $payload = [
            'Query' => [
                'limit' => 200,
                'conditions' => [
                    [
                        'column' => 'datetime',
                        'operator' => 3,
                        'values' => [
                            $startOfDay,
                            $endOfDay,
                        ],
                    ],
                    [
                        'column' => 'device_id',
                        'operator' => 2,
                        'values' => [
                            $request->device_id,
                        ],
                    ],
                    [
                        'column' => 'event_type_id',
                        'operator' => 2,
                        'values' => [
                            '4102',
                        ],
                    ],
                ],
                'orders' => [
                    [
                        'column' => 'datetime',
                        'descending' => true,
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $request->header()['bs-session-id'][0],
                ])
                ->post($url, $payload);

            if ($response->successful()) {
                $events = $response->json()['EventCollection']['rows'];

                $employeeIds = array_column($events, 'user_id.user_id');
                $employees = Employee::whereIn('id', $employeeIds)->get();

                $employeesById = $employees->keyBy('id');

                $result = array_map(function ($event) {
                    $employeeId = $event['user_id']['user_id'];

                    $resultEmployee = Employee::find($employeeId);

                    return [
                        'datetime' => Carbon::parse($event['server_datetime'])->format('Y-m-d H:i:s'),
                        'employee_name' => $resultEmployee->fullname,
                        'employee_id' => $employeeId,
                        'department' => $resultEmployee->department->name,
                        'employee_status' => 'დაშვებულია',
                        'device_id' => $event['device_id']['id'],
                        'device_name' => $event['device_id']['name'],
                    ];
                }, $events);

                return response()->json($result);
            } else {
                return response()->json(['error' => 'Failed to fetch events', 'response' => $response->json()], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateAccessGroups(Request $request, $id)
    {
       
        $url = "https://10.150.20.173/api/access_groups/{$id}";
        $payload = [
            'AccessGroup' => [
                'new_users' => $request->input('new_users', []),
            ],
        ];

        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $request->header('bs-session-id'),
                ])
                ->put($url, $payload);

            if ($response->successful()) {
                return response()->json($response->json(), 200);
            } else {
                return response()->json([
                    'error' => 'Failed to update access groups',
                    'response' => $response->json(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteEmployeeFromAccessGroup(Request $request, $id)
    {
        $url = "https://10.150.20.173/api/access_groups/{$id}";

        $payload = [
            'AccessGroup' => [
                'delete_users' => $request->input('delete_users', []),
            ],
        ];

        try {
            $response = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $request->header('bs-session-id'),
                ])
                ->delete($url, $payload);

            if ($response->successful()) {
                return response()->json($response->json(), 200);
            } else {
                return response()->json([
                    'error' => 'Failed to delete user from access group',
                    'response' => $response->json(),
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getEmployeeWithBuildings(Request $request)
    {
        $sessionId = '278ce0774d064a909311050b765bed72';
        $biostarUrl = 'https://10.150.20.173/api/users';

        // Fetch users from Biostar API
        $biostarResponse = Http::withOptions(['verify' => false])
            ->withHeaders(['bs-session-id' => $sessionId])
            ->get($biostarUrl);

        if (!$biostarResponse->successful()) {
            return response()->json(['error' => 'Failed to fetch users from Biostar API', 'response' => $biostarResponse->json()], $biostarResponse->status());
        }

        $biostarUsers = $biostarResponse->json()['UserCollection']['rows'] ?? [];

        // Fetch employees and their associated data from the database
        $employeeQuery = Employee::with('schedule', 'department.buildings', 'dayDetails.dayType', 'holidays');

        if ($request->has('employee_id')) {
            $employeeQuery->where('id', $request->input('employee_id'));
        }

        $employees = $employeeQuery->get()->keyBy('id');

        $buildingId = $request->input('building_id');

        // Merge and format data
        $mergedData = [];
        foreach ($biostarUsers as $user) {
            $employee = $employees[$user['user_id']] ?? null;

            if ($employee && $employee->department && $employee->department->buildings) {
                foreach ($employee->department->buildings as $building) {
                    if ($buildingId && $building->id != $buildingId) {
                        continue;
                    }
                    
                    $employeeAccessGroupIds = array_column($user['access_groups'], 'id');
                    $isAccessGroupMatch = in_array($building->access_group, $employeeAccessGroupIds);

                    $mergedData[] = [
                        'user_id' => $user['user_id'],
                        'fullname' => $employee->fullname ?? $user['name'],
                        'personal_id' => $employee->personal_id,
                        'department' => $employee->department->name,
                        'employee_access_group' => $user['access_groups'],
                        'building' => [
                            'id' => $building->id,
                            'name' => $building->name,
                            'access_group' => $building->access_group ?? null,
                            'is_access_group_match' => $isAccessGroupMatch,
                        ],
                        'is_not_accessed' => !$isAccessGroupMatch,
                    ];
                }
            } else {
                $mergedData[] = [
                    'user_id' => $user['user_id'],
                    'fullname' => $user['name'],
                    'email' => $user['email'] ?? null,
                    'department' => $employee ? $employee->department->name : null,
                    'building' => null,
                    'is_not_accessed' => true,
                ];
            }
        }

        return response()->json($mergedData);
    }


}
