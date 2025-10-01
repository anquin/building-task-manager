<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Building;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        $buildingId = $this->input('building_id');
        
        if (!$user || !$buildingId) {
            return false;
        }
        
        $building = Building::find($buildingId);
        
        if (!$building) {
            return false;
        }
        
        Log::info([$user->role, UserRole::OWNER->value, $buildingId, $building->id]);
        return $user->building_id === $building->id
            && $user->role === UserRole::OWNER->value;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'building_id' => 'required|exists:buildings,id',
            'assignee' => 'sometimes|nullable|exists:users,id',
            'summary' => 'required|string|max:255',
        ];
    }
}
