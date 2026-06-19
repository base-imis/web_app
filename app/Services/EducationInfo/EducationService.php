<?php

namespace App\Services\EducationInfo;
use Illuminate\Http\Request;
use App\Models\EducationInfo\School;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


Class EducationService
{
    public function storeSchoolInformation(Request $request)
    {

        /* dd($request->all()); */
        DB::beginTransaction();
        try {
            // Generate Custom School ID
            $maxSchoolId = School::withTrashed()
                ->whereNotNull('custom_school_id')
                ->max(DB::raw("CAST(SUBSTRING(custom_school_id, 2) AS INTEGER)"));
            $nextId = $maxSchoolId ? $maxSchoolId + 1 : 1;
            $generatedSchoolId = 'S' . sprintf('%06d', $nextId);

            $school = new School();
            $school->custom_school_id = $generatedSchoolId;
            $school->bin = $request->bin;

            // Fill basic info
            $school->name = $request->name;
            $school->headteacher_name = $request->headteacher_name;
            $school->contact_person_name = $request->contact_person_name;
            $school->contact_person_number = $request->contact_person_number;
            $school->ward_no = $request->ward_no;
            $school->location_name = $request->location_name;
            $school->main_building_structure_type = $request->main_building_structure_type;
            $school->main_building_floors = $request->main_building_floors;
            $school->associate_buildings_count = $request->associate_buildings_count;

            // School types
            $school->school_type_pre_primary = $request->has('school_type_pre_primary');
            $school->school_type_basic_1_5 = $request->has('school_type_basic_1_5');
            $school->school_type_basic_6_8 = $request->has('school_type_basic_6_8');
            $school->school_type_secondary_9_10 = $request->has('school_type_secondary_9_10');
            $school->school_type_secondary_9_12 = $request->has('school_type_secondary_9_12');

            // Students
            $studentFields = [
                'pre_primary', 'basic_1_5', 'basic_6_8', 'secondary_9_10', 'secondary_9_12'
            ];
            foreach ($studentFields as $field) {
                $school->{$field . '_girls_count'} = $request->{$field . '_girls_count'} ?? 0;
                $school->{$field . '_boys_count'} = $request->{$field . '_boys_count'} ?? 0;
                $school->{$field . '_other_count'} = $request->{$field . '_other_count'} ?? 0;
                $school->{$field . '_total_count'} = $request->{$field . '_total_count'} ?? 0;
            }

            $school->total_girls = $request->total_girls ?? 0;
            $school->total_boys = $request->total_boys ?? 0;
            $school->total_other = $request->total_other ?? 0;
            $school->total_students = $request->total_students ?? 0;

            // Staff
            $school->teachers_male = $request->teachers_male ?? 0;
            $school->teachers_female = $request->teachers_female ?? 0;
            $school->teachers_other = $request->teachers_other ?? 0;
            $school->teachers_total = $request->teachers_total ?? 0;

            // Toilets, urinals, handwash
            $groups = ['toilet', 'urinal', 'handwash'];
            foreach ($groups as $group) {
                foreach (['teacher', 'student'] as $role) {
                    foreach (['male', 'female', 'other', 'total'] as $gender) {
                        $col = "{$group}_{$role}_{$gender}";
                        $school->{$col} = $request->{$col} ?? 0;
                    }
                }
            }

            $school->universal_design_toilet_count = $request->universal_design_toilet_count ?? 0;
            $school->main_toilet_type = $request->main_toilet_type;
            $school->toilet_connection = $request->toilet_connection;
            $school->septic_outlet = $request->septic_outlet;
            $school->pit_outlet = $request->pit_outlet;
            $school->soap_and_water_available = $request->soap_and_water_available ?? 0;
            $school->main_drinking_water_source = $request->main_drinking_water_source;
            $school->last_registration_renewal_date = $request->last_registration_renewal_date;

            // Handle certificate file upload
            if ($request->hasFile('certificate_picture_url')) {
                $path = $request->file('certificate_picture_url')->store('certificates', 'public');
                $school->certificate_picture_url = $path;
            }

            $school->save();

            DB::commit();
            return redirect()->route('education.school.index')->with('success', 'School information stored successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to store school information: ' . $e->getMessage()], 500);
        }
    }

    public function updateSchoolInformation($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $school = School::findOrFail($id);

            // Fill basic info
            $school->bin = $request->bin;
            $school->name = $request->name;
            $school->headteacher_name = $request->headteacher_name;
            $school->contact_person_name = $request->contact_person_name;
            $school->contact_person_number = $request->contact_person_number;
            $school->ward_no = $request->ward_no;
            $school->location_name = $request->location_name;
            $school->main_building_structure_type = $request->main_building_structure_type;
            $school->main_building_floors = $request->main_building_floors;
            $school->associate_buildings_count = $request->associate_buildings_count;

            // School types
            $school->school_type_pre_primary = $request->has('school_type_pre_primary');
            $school->school_type_basic_1_5 = $request->has('school_type_basic_1_5');
            $school->school_type_basic_6_8 = $request->has('school_type_basic_6_8');
            $school->school_type_secondary_9_10 = $request->has('school_type_secondary_9_10');
            $school->school_type_secondary_9_12 = $request->has('school_type_secondary_9_12');

            // Students
            $studentFields = [
                'pre_primary', 'basic_1_5', 'basic_6_8', 'secondary_9_10', 'secondary_9_12'
            ];
            foreach ($studentFields as $field) {
                $school->{$field . '_girls_count'} = $request->{$field . '_girls_count'} ?? 0;
                $school->{$field . '_boys_count'} = $request->{$field . '_boys_count'} ?? 0;
                $school->{$field . '_other_count'} = $request->{$field . '_other_count'} ?? 0;
                $school->{$field . '_total_count'} = $request->{$field . '_total_count'} ?? 0;
            }

            $school->total_girls = $request->total_girls ?? 0;
            $school->total_boys = $request->total_boys ?? 0;
            $school->total_other = $request->total_other ?? 0;
            $school->total_students = $request->total_students ?? 0;

            // Staff
            $school->teachers_male = $request->teachers_male ?? 0;
            $school->teachers_female = $request->teachers_female ?? 0;
            $school->teachers_other = $request->teachers_other ?? 0;
            $school->teachers_total = $request->teachers_total ?? 0;

            // Toilets, urinals, handwash
            $groups = ['toilet', 'urinal', 'handwash'];
            foreach ($groups as $group) {
                foreach (['teacher', 'student'] as $role) {
                    foreach (['male', 'female', 'other', 'total'] as $gender) {
                        $col = "{$group}_{$role}_{$gender}";
                        $school->{$col} = $request->{$col} ?? 0;
                    }
                }
            }

            $school->universal_design_toilet_count = $request->universal_design_toilet_count ?? 0;
            $school->main_toilet_type = $request->main_toilet_type;
            $school->toilet_connection = $request->toilet_connection;
            $school->septic_outlet = $request->septic_outlet;
            $school->pit_outlet = $request->pit_outlet;
            $school->soap_and_water_available = $request->soap_and_water_available ?? 0;
            $school->main_drinking_water_source = $request->main_drinking_water_source;
            $school->last_registration_renewal_date = $request->last_registration_renewal_date;

            // Handle certificate update
            if ($request->hasFile('certificate_picture_url')) {
                if ($school->certificate_picture_url && \Storage::disk('public')->exists($school->certificate_picture_url)) {
                    \Storage::disk('public')->delete($school->certificate_picture_url);
                }
                $path = $request->file('certificate_picture_url')->store('certificates', 'public');
                $school->certificate_picture_url = $path;
            }

            $school->save();
            DB::commit();

            return redirect()->route('education.school.index')->with('success', 'School information updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update school information: ' . $e->getMessage()], 500);
        }
    }

}
