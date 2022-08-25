<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\{ Request, Response };
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::with('company')->orderBy('first_name', 'ASC')->get();

        return response()->json([
            'message' => 'Showing all employees',
            'data' => $employees
        ], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company_id' => 'required|integer|exists:companies,id',
            'email' => 'required|string|email:rfc,dns|unique:employees,email',
            'phone' => 'required|string|max:255|unique:employees,phone'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $employee = Employee::create($request->all());

            return response()->json([
                'message' => 'Employee\'s data has been added',
                'data' => $employee
            ], Response::HTTP_CREATED);
        } catch (QueryException $E) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $employee = Employee::with('company')->firstWhere('id', $id);

        if (!$employee) {
            return response()->json([
                'message' => 'The data provided was not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Showing employee profiles',
            'data' => $employee
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'message' => 'The data provided was not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $email_rules = ($request->email == $employee->email) ? 'required' : 'required|email:rfc,dns|unique:employees,email';
        $phone_rules = ($request->phone == $employee->phone) ? 'required' : 'required|string|max:255|unique:employees,phone';

        $validate = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'company_id' => 'required|integer|exists:companies,id',
            'email' => $email_rules,
            'phone' => $phone_rules
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $employee->update($request->all());

            return response()->json([
                'message' => 'Employee\'s data has been updated',
                'data' => $employee
            ], Response::HTTP_OK);
        } catch (QueryException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json([
                'message' => 'The data provided was not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $employee->delete();

        return response()->json([
            'message' => 'Employee\'s data has been deleted',
        ], Response::HTTP_GONE);
    }
}
