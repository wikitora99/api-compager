<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\{ Request, Response };
use Illuminate\Support\Facades\{ Validator, Storage };
use Illuminate\Database\QueryException;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::with('employees')->orderBy('name', 'ASC')->get();
        
        return response()->json([
            'message' => 'Showing all companies',
            'data' => $companies
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email:rfc,dns|unique:companies,email',
            'logo' => 'required|image|mimes:png,jpg,jpeg|max:1024',
            'website' => 'required|url|unique:companies,website'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $logo_path = Storage::disk('public')->putFile('logos', $request->file('logo'));

            $company = Company::create([
                'name' => $request->name,
                'email' => $request->email,
                'logo' => $logo_path,
                'website' => $request->website
            ]);

            return response()->json([
                'message' => 'Company has been added',
                'data' => $company
            ], Response::HTTP_CREATED);
        } catch (QueryException $e) {
            if (isset($logo_path)) {
                Storage::disk('public')->delete($logo_path);
            }

            return response()->json([
                'error' => $e->getMessage()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::with('employees')->firstWhere('id', $id);

        if (!$company) {
            return response()->json([
                'message' => 'The data provided was not found'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Showing company profiles',
            'data' => $company
        ], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'message' => 'The data provided was not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $email_rules = ($request->email == $company->email) ? 'required' : 'required|email:rfc,dns|unique:companies,email';
        $website_rules = ($request->website == $company->website) ? 'required' : 'required|url|unique:companies,website';

        $validate = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => $email_rules,
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:1024',
            'website' => $website_rules
        ]);

        if ($validate->fails()) {
            return response()->json([
                'error' => $validate->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            if ($request->hasFile('logo')) {
                Storage::disk('public')->delete($company->logo);

                $logo_path = Storage::disk('public')->putFile('logos', $request->file('logo'));
            }

            $company->update([
                'name' => $request->name,
                'email' => $request->email,
                'logo' => $logo_path ?? $company->logo,
                'website' => $request->website
            ]);

            return response()->json([
                'message' => 'Company\'s data has been updated',
                'data' => $company
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
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $company = Company::find($id);

        if (!$company) {
            return response()->json([
                'message' => 'The data provided was not found'
            ], Response::HTTP_NOT_FOUND);
        }

        if (Storage::disk('public')->exists($company->logo)) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->delete();

        return response()->json([
            'message' => 'Comapny\'s data has been deleted',
        ], Response::HTTP_GONE);
    }
}
