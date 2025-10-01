<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuildingResource;
use App\Models\Building;

class BuildingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buildings = Building::all();
        return BuildingResource::collection($buildings);
    }

    /**
     * Display the specified resource.
     */
    public function show(Building $building)
    {
        // $this->authorize('view', $building);
        return new BuildingResource($building);
    }
}
