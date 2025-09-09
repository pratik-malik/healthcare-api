<?php

namespace App\Http\Controllers;

use App\Http\Resources\HealthcareProfessionalResource;
use App\Models\HealthcareProfessional;
use Illuminate\Http\Request;
use Throwable;

class HealthcareProfessionalController extends BaseController
{
    /**
     * Display a paginated list of healthcare professionals.
     *
     * Optionally filters by specialty if provided in the request query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = HealthcareProfessional::query();

            if ($request->filled('specialty')) {
                $query->where('specialty', $request->query('specialty'));
            }

            $professionals = $query->paginate(20);
            $meta = [
                'current_page' => $professionals->currentPage(),
                'last_page' => $professionals->lastPage(),
                'per_page' => $professionals->perPage(),
                'total' => $professionals->total(),
            ];

            return $this->sendResponse(
                HealthcareProfessionalResource::collection($professionals),
                'Healthcare professionals fetched successfully',
                $meta
            );
        } catch (Throwable $e) {
            return $this->sendError('Unable to fetch healthcare professionals', ['error' => $e->getMessage()], 500);
        }
    }
}
