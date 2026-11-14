<?php
// Last Modified Date: 23-09-2026
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)
namespace App\Http\Requests\Fsm;

use App\Models\BuildingInfo\Building;
use App\Services\OwnerPiiPresenter;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $isConfirm = $this->input('action_type') === 'confirm' || session('action_type') === 'confirm';
        if ($isConfirm) {
            $scheduleAccept = session('schedule_accept');

            if (is_array($scheduleAccept)) {
                $lockedValues = array_filter([
                    'road_code' => $scheduleAccept['road_code'] ?? null,
                    'bin' => $scheduleAccept['bin'] ?? null,
                    'containment_id' => $scheduleAccept['containment_id'] ?? null,
                    'ward' => $scheduleAccept['ward'] ?? null,
                    'service_provider_id' => $scheduleAccept['service_provider_id'] ?? null,
                ], function ($value) {
                    return $value !== null && $value !== '';
                });

                $this->merge($lockedValues);
            }
        }

        $isAnf = $this->input('is_anf') == '1';
        $bin = $this->input('bin');

        if (!$isAnf && !empty($bin)) {
            $building = Building::where('bin', $bin)->first();
            $sourceOwner = $building ? $building->owners : null;
            $ownerPii = $sourceOwner ? app(OwnerPiiPresenter::class)->presentPlaintext($sourceOwner) : [];

            $isNameMasked = is_string($this->customer_name) && preg_match('/^\*+$/', trim($this->customer_name));
            $isGenderMasked = is_string($this->customer_gender) && (preg_match('/^\*+$/', trim($this->customer_gender)) || trim($this->customer_gender) === '');
            $isContactMasked = is_string($this->customer_contact) && preg_match('/^\*+$/', trim($this->customer_contact));

            $mergedOwner = [];

            if ($isNameMasked || empty($this->customer_name)) {
                $mergedOwner['customer_name'] = !empty($ownerPii['owner_name'])
                    ? $ownerPii['owner_name']
                    : ($building ? ($building->house_number ?? $building->bin) : 'Owner');
            }

            if ($isGenderMasked || empty($this->customer_gender)) {
                $mergedOwner['customer_gender'] = !empty($ownerPii['owner_gender'])
                    ? $ownerPii['owner_gender']
                    : 'Male';
            }

            if ($isContactMasked || empty($this->customer_contact)) {
                $contact = !empty($ownerPii['owner_contact'])
                    ? preg_replace('/[^0-9]/', '', (string) $ownerPii['owner_contact'])
                    : null;
                if ($contact && strlen($contact) === 10) {
                    $mergedOwner['customer_contact'] = $contact;
                } else {
                    $mergedOwner['customer_contact'] = null;
                }
            }

            if (!empty($mergedOwner)) {
                $this->merge($mergedOwner);
            }
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $isAnf = request()->input('is_anf') == '1';
        $isConfirm = request()->input('action_type') === 'confirm' || session('action_type') === 'confirm';
        $isCustomerContactMasked = is_string($this->customer_contact) && preg_match('/^\*+$/', trim($this->customer_contact));

        return [
            'road_code'                   => !$isAnf && !$isConfirm && request()->isMethod('post') ? 'required' : 'nullable',
            'bin'                         => !$isAnf && !$isConfirm && request()->isMethod('post') ? 'required' : 'nullable',
            'containment_id'              => 'nullable|string',
            'action_type'                 => 'nullable|in:confirm',
            'ward'                        => 'nullable|integer|min:1',

            // ANF fields
            'anf_ward'                    => 'nullable|integer|min:1',
            'anf_locality'                => 'nullable|string|max:255',
            'anf_nearest_locality'        => 'nullable|string|max:255',
            'is_anf'                      => 'nullable',

            // Owner fields
            'customer_name'               => !$isAnf ? 'required' : 'nullable',
            'customer_gender'             => !$isAnf ? 'required' : 'nullable',
            'customer_contact'            => !$isAnf
                ? ($isCustomerContactMasked ? 'nullable' : 'nullable|integer|digits:10')
                : 'nullable',

            // Applicant fields
            'applicant_name'              => 'required',
            'applicant_gender'            => 'required',
            'applicant_contact'           => 'required|integer|digits:10',

            'proposed_emptying_date'      => $isConfirm ? 'required|date|after_or_equal:today' : 'nullable',
            'service_provider_id'         => $isConfirm ? 'nullable|integer' : 'required|integer',
            'landmark'                    => 'nullable',
            'emergency_desludging_status' => 'nullable|boolean',
            'household_served'            => 'nullable|integer|min:1',
            'population_served'           => 'nullable|integer|min:1',
            'toilet_count'                => 'nullable|integer|min:1',
            'desludging_vehicle_size'     => $isConfirm ? 'nullable' : 'nullable|integer',
        ];
    }

    /**
     * Get the error messages to display if validation fails.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'road_code.required' => __('The Street Name/ Street Code is required.'),
            'bin.required' => __('The House Number / BIN is required.'),
            'containment_id.required_if' => __('The Containment ID is required.'),
            'ward.integer' => __('The Ward must be an integer.'),
            'ward.min' => __('The Ward must be at least 1.'),
            'customer_contact.integer' => __('The owner contact must be an integer.'),
            'customer_contact.digits' => __('Please enter a valid 10-digit Owner Contact Number.'),
            'customer_name.required' => __('The Owner Name is required.'),
            'customer_gender.required' => __('The Owner Gender is required.'),
            'applicant_name.required' => __('The Applicant Name is required.'),
            'applicant_gender.required' => __('The Applicant Gender is required.'),
            'applicant_contact.required' => __('The Applicant Contact (Phone) is required.'),
            'applicant_contact.digits' => __('Please enter a valid 10-digit Applicant Contact Number.'),
            'proposed_emptying_date.required' => __('The Proposed Emptying Date is required.'),
            'proposed_emptying_date.after_or_equal' => __('The Proposed Emptying Date must be today or a future date.'),
            'service_provider_id.required' => __('The Service Provider Name is required.'),
            'household_served.integer' => __('The Household Served must be an integer.'),
            'household_served.min' => __('The Household Served must be at least 1.'),
            'population_served.integer' => __('The Population Served must be an integer.'),
            'population_served.min' => __('The Population Served must be at least 1.'),
            'toilet_count.integer' => __('The Toilet Count must be an integer.'),
            'toilet_count.min' => __('The Toilet Count must be at least 1.'),
        ];
    }
}
