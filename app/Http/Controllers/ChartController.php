<?php

namespace App\Http\Controllers;

use App\Services\Cwis\CwisIndicatorService;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartController extends Controller
{
    public function showChart()
    {
        return view('chart');
    }

    public function downloadPDF(Request $request)
    {
        abort_unless($request->user() && $request->user()->can('List CWIS'), 403);
        $validated = $request->validate([
            'year' => ['required', 'integer', 'between:1,9999'],
            'country_code' => ['nullable', Rule::in(array_keys(CwisIndicatorService::COUNTRIES))],
        ]);
        $year = (int) $validated['year'];
        $indicators = app(CwisIndicatorService::class);
        // Settings are authoritative; an already-open dashboard may carry an old country.
        $country = $indicators->activeCountry();

        $html = view('cwis.cwis-dashboard.chart-layout.country-pdf-report', [
            'catalogue' => $indicators->forCountry($country),
            'values' => $indicators->resultsForCountry($year, $country)->pluck('data_value', 'indicator_code'),
            'countryLabel' => __(CwisIndicatorService::COUNTRIES[$country]),
            'reportingYear' => $year,
        ])->render();

        try {
            return PDF::loadHTML($html)->download('CWIS_Report_' . $country . '_' . $year . '.pdf')
                ->header('X-CWIS-Country', $country)
                ->header('Cache-Control', 'private, no-store, max-age=0');
        } catch (\RuntimeException $exception) {
            report($exception);
            return response()->json([
                'message' => __('The PDF could not be generated. Ask the administrator to check the PDF renderer and writable temporary storage.'),
            ], 503);
        }
    }
}
