<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    // Create a new package
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description' => 'required|string',
            'description_ar' => 'required|string',
            'features' => 'required|array',
            'features_ar' => 'required|array',
            'price_dollar' => 'required|numeric',
            'price_EGP' => 'required|numeric',
            'max_visitor' => 'required|integer',
        ]);




        $package = Package::create($validatedData);

        return response()->json(['message' => 'Package created successfully', 'data' => $package], 201);
    }

    // Update a package
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'description_ar' => 'sometimes|required|string',
            'price_dollar' => 'sometimes|required|numeric',
            'price_EGP' => 'sometimes|required|numeric',
            'max_visitor' => 'sometimes|required|integer',
        ]);

        $package = Package::find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        $package->update($validatedData);

        return response()->json(['message' => 'Package updated successfully', 'data' => $package]);
    }




    public function destroy($id)
    {
        $package = Package::find($id);

        if (!$package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        $package->delete();

        return response()->json(['message' => 'Package deleted successfully']);
    }



public function index()
{
    $packages = Package::all()->map(function ($package) {
        $features = $package->features;

        // Decode if it's stored as JSON
        if (is_string($features)) {
            $features = json_decode($features, true);
        }

        // Normalize features into key => value format
        $normalizedFeatures = [];
        foreach ($features as $key => $value) {
            if (is_int($key)) {
                $normalizedFeatures[$value] = $value;
            } else {
                $normalizedFeatures[$key] = $value;
            }
        }

        $package->features = $normalizedFeatures;
        return $package;
    });

    return response()->json($packages);
}



}
