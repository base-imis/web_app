@extends('layouts.dashboard')
@section('title', 'Education Dashboard')

@section('content')
@php
    $totalStudents = (int) ($enrollmentSummary->total_students ?? 0);
    $totalGirls = (int) ($enrollmentSummary->total_girls ?? 0);
    $totalBoys = (int) ($enrollmentSummary->total_boys ?? 0);
    $totalOther = (int) ($enrollmentSummary->total_other ?? 0);
    $teachersTotal = (int) ($enrollmentSummary->teachers_total ?? 0);
    $supportStaffTotal = (int) ($enrollmentSummary->support_staff_total ?? 0);
    $maxSchoolType = max(1, collect($schoolTypeRows)->max('count') ?: 1);
    $maxWardStudents = max(1, $wardStats->max('students') ?: 1);
    $maxWaterSource = max(1, $waterSourceStats->max('count') ?: 1);
    $maxToiletConnection = max(1, $toiletConnectionStats->max('count') ?: 1);
    $genderTotal = max(1, $totalGirls + $totalBoys + $totalOther);
@endphp

<style>
    .education-dashboard {
        color: #27313f;
    }

    .education-dashboard .dashboard-actions {
        margin-bottom: 12px;
        text-align: right;
    }

    .education-dashboard .section-title {
        display: block;
        font-size: 18px;
        font-weight: 500;
        line-height: 1.2;
        margin: 0 0 12px 0;
        padding: 0;
        text-align: left;
    }

    .metric-card {
        align-items: stretch;
        background: #fff;
        border: 1px solid #d9dee7;
        display: flex;
        margin-bottom: 16px;
        min-height: 86px;
    }

    .metric-icon {
        align-items: center;
        background: #17a2b8;
        color: #fff;
        display: flex;
        font-size: 26px;
        justify-content: center;
        min-width: 72px;
    }

    .metric-content {
        padding: 12px 14px;
        width: 100%;
    }

    .metric-label {
        color: #4b5563;
        font-size: 12px;
        font-weight: 600;
    }

    .metric-value {
        color: #172033;
        font-size: 22px;
        font-weight: 600;
        line-height: 1.1;
        margin-top: 4px;
    }

    .metric-note {
        color: #6b7280;
        font-size: 12px;
        margin-top: 4px;
    }

    .analysis-card {
        background: #fff;
        border: 1px solid #d9dee7;
        border-top: 3px solid #17a2b8;
        margin-bottom: 16px;
    }

    .analysis-card.equal-height {
        display: flex;
        flex-direction: column;
        height: auto;
    }

    .analysis-card.equal-height .card-body {
        flex: 1;
        min-height: 220px;
    }

    .education-dashboard .equal-row {
        display: flex;
        flex-wrap: wrap;
    }

    .education-dashboard .equal-row:before,
    .education-dashboard .equal-row:after {
        display: none;
    }

    .education-dashboard .equal-row > [class*="col-"] {
        display: flex;
    }

    .education-dashboard .equal-row .analysis-card {
        width: 100%;
    }

    .education-dashboard .equal-row .analysis-card.equal-height {
        flex: 1;
    }

    .education-dashboard .equal-row .metric-card {
        width: 100%;
    }

    @media (max-width: 1199px) {
        .education-dashboard .equal-row {
            display: block;
        }

        .education-dashboard .equal-row > [class*="col-"] {
            display: block;
        }
    }

    .analysis-card .card-heading {
        align-items: center;
        border-bottom: 1px solid #edf0f5;
        display: flex;
        justify-content: space-between;
        padding: 14px 16px;
    }

    .analysis-card .card-heading h3 {
        font-size: 14px;
        font-weight: 500;
        margin: 0;
    }

    .analysis-card .card-heading span {
        color: #6b7280;
        font-size: 12px;
    }

    .analysis-card .card-body {
        padding: 16px;
    }

    .bar-row {
        display: grid;
        grid-template-columns: minmax(130px, 180px) 1fr 64px;
        gap: 12px;
        align-items: center;
        margin-bottom: 12px;
    }

    .bar-label {
        color: #374151;
        font-size: 13px;
        font-weight: 600;
    }

    .bar-track {
        background: #eef2f7;
        height: 12px;
        overflow: hidden;
    }

    .bar-fill {
        background: #8cccf0;
        height: 100%;
        min-width: 2px;
    }

    .bar-value {
        color: #111827;
        font-size: 13px;
        font-weight: 700;
        text-align: right;
    }

    .gender-stack {
        display: flex;
        height: 18px;
        overflow: hidden;
        background: #eef2f7;
    }

    .gender-stack span {
        display: block;
        min-width: 2px;
    }

    .legend-row {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 12px;
    }

    .legend-item {
        align-items: center;
        color: #4b5563;
        display: flex;
        font-size: 12px;
        gap: 6px;
    }

    .legend-swatch {
        height: 10px;
        width: 10px;
    }

    .insight-list {
        margin: 0;
        padding-left: 18px;
    }

    .insight-list li {
        margin-bottom: 8px;
    }

    .dashboard-table {
        margin-bottom: 0;
    }

    .dashboard-table th {
        background: #f7f9fc;
        border-top: 0;
        color: #4b5563;
        font-size: 12px;
        text-transform: uppercase;
    }

    .dashboard-table td {
        vertical-align: middle;
    }

    @media (max-width: 767.98px) {
        .bar-row {
            grid-template-columns: 1fr 48px;
        }

        .bar-label {
            grid-column: 1 / -1;
        }
    }
</style>

<div class="education-dashboard">
    <div class="dashboard-actions">
        <a href="{{ route('education.school.index') }}" class="btn btn-info">School List</a>
    </div>

    <h1 class="section-title">{{ __('Overview') }}</h1>
    <div class="row equal-row">
        <div class="col-lg-3 col-md-6">
            <div class="metric-card">
                <div class="metric-icon"><i class="fas fa-school"></i></div>
                <div class="metric-content">
                    <div class="metric-label">Schools</div>
                    <div class="metric-value">{{ number_format($totalSchools) }}</div>
                    <div class="metric-note">{{ number_format($coverageSummary['wards_covered']) }} wards represented</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="metric-card">
                <div class="metric-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="metric-content">
                    <div class="metric-label">Students</div>
                    <div class="metric-value">{{ number_format($totalStudents) }}</div>
                    <div class="metric-note">{{ number_format($coverageSummary['avg_students_per_school'], 1) }} average per school</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="metric-card">
                <div class="metric-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="metric-content">
                    <div class="metric-label">Teachers</div>
                    <div class="metric-value">{{ number_format($teachersTotal) }}</div>
                    <div class="metric-note">{{ number_format($coverageSummary['student_teacher_ratio'], 1) }} students per teacher</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="metric-card">
                <div class="metric-icon"><i class="fas fa-tint"></i></div>
                <div class="metric-content">
                    <div class="metric-label">Soap and Water</div>
                    <div class="metric-value">{{ number_format($coverageSummary['soap_water_coverage'], 1) }}%</div>
                    <div class="metric-note">{{ number_format($sanitationSummary->soap_water_available_schools ?? 0) }} schools reported available</div>
                </div>
            </div>
        </div>
    </div>

    <h1 class="section-title">{{ __('Education Profile') }}</h1>
    <div class="row equal-row">
        <div class="col-lg-6">
            <div class="analysis-card equal-height">
                <div class="card-heading">
                    <h3>School Level Coverage</h3>
                    <span>Schools can serve more than one level</span>
                </div>
                <div class="card-body">
                    @foreach ($schoolTypeRows as $row)
                        <div class="bar-row">
                            <div class="bar-label">{{ $row['label'] }}</div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: {{ ($row['count'] / $maxSchoolType) * 100 }}%"></div>
                            </div>
                            <div class="bar-value">{{ number_format($row['count']) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="analysis-card equal-height">
                <div class="card-heading">
                    <h3>Enrollment Gender Mix</h3>
                    <span>Total {{ number_format($totalStudents) }}</span>
                </div>
                <div class="card-body">
                    <div class="gender-stack">
                        <span style="width: {{ ($totalGirls / $genderTotal) * 100 }}%; background:#2f6f9f"></span>
                        <span style="width: {{ ($totalBoys / $genderTotal) * 100 }}%; background:#56a3a6"></span>
                        <span style="width: {{ ($totalOther / $genderTotal) * 100 }}%; background:#a77942"></span>
                    </div>
                    <div class="legend-row">
                        <div class="legend-item"><span class="legend-swatch" style="background:#2f6f9f"></span>Female {{ number_format($totalGirls) }}</div>
                        <div class="legend-item"><span class="legend-swatch" style="background:#56a3a6"></span>Male {{ number_format($totalBoys) }}</div>
                        <div class="legend-item"><span class="legend-swatch" style="background:#a77942"></span>Other {{ number_format($totalOther) }}</div>
                    </div>
                    <hr>
                    <ul class="insight-list">
                        <li>{{ number_format($primarySchools) }} schools include Basic levels.</li>
                        <li>{{ number_format($secondarySchools + $higherSecondarySchools) }} schools include Secondary or higher secondary.</li>
                        <li>{{ number_format($supportStaffTotal) }} support staff recorded.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="row equal-row">
        <div class="col-lg-6">
            <div class="analysis-card equal-height">
                <div class="card-heading">
                    <h3>Ward Enrollment Distribution</h3>
                    <span>Students and school count by ward</span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table dashboard-table table-sm">
                        <thead>
                            <tr>
                                <th>Ward</th>
                                <th>Schools</th>
                                <th>Students</th>
                                <th style="width: 45%">Enrollment Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($wardStats as $ward)
                                <tr>
                                    <td>{{ $ward->ward_no ?: 'Not Recorded' }}</td>
                                    <td>{{ number_format($ward->schools) }}</td>
                                    <td>{{ number_format($ward->students) }}</td>
                                    <td>
                                        <div class="bar-track">
                                            <div class="bar-fill" style="width: {{ ($ward->students / $maxWardStudents) * 100 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4">No ward data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="analysis-card equal-height">
                <div class="card-heading">
                    <h3>Sanitation Capacity</h3>
                    <span>Reported seats and units</span>
                </div>
                <div class="card-body">
                    @foreach ([
                        'Toilet seats' => $sanitationSummary->toilet_total ?? 0,
                        'Urinal seats' => $sanitationSummary->urinal_total ?? 0,
                        'Handwashing units' => $sanitationSummary->handwash_total ?? 0,
                        'Universal design toilets' => $sanitationSummary->universal_design_toilet_total ?? 0,
                    ] as $label => $value)
                        <div class="bar-row">
                            <div class="bar-label">{{ $label }}</div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: {{ ($value / max(1, $sanitationSummary->toilet_total ?? 1)) * 100 }}%"></div>
                            </div>
                            <div class="bar-value">{{ number_format($value) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <div class="row equal-row">
        <div class="col-lg-6">
            <div class="analysis-card equal-height">
                <div class="card-heading">
                    <h3>Toilet Connection</h3>
                    <span>Schools by connection type</span>
                </div>
                <div class="card-body">
                    @foreach ($toiletConnectionStats as $item)
                        <div class="bar-row">
                            <div class="bar-label">{{ $item->label }}</div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: {{ ($item->count / $maxToiletConnection) * 100 }}%"></div>
                            </div>
                            <div class="bar-value">{{ number_format($item->count) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="analysis-card equal-height">
                <div class="card-heading">
                    <h3>Drinking Water Source</h3>
                    <span>Primary source by school</span>
                </div>
                <div class="card-body">
                    @foreach ($waterSourceStats as $item)
                        <div class="bar-row">
                            <div class="bar-label">{{ $item->label }}</div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: {{ ($item->count / $maxWaterSource) * 100 }}%"></div>
                            </div>
                            <div class="bar-value">{{ number_format($item->count) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="analysis-card">
                <div class="card-heading">
                    <h3>Largest Schools by Enrollment</h3>
                    <span>Top {{ $topSchoolsByStudents->count() }} schools</span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table dashboard-table table-sm">
                        <thead>
                            <tr>
                                <th>School</th>
                                <th>Ward</th>
                                <th>Students</th>
                                <th>Toilet Connection</th>
                                <th>Water Source</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topSchoolsByStudents as $school)
                                <tr>
                                    <td>
                                        <a href="{{ route('education.school.show', ['id' => $school->custom_school_id]) }}">
                                            {{ $school->name }}
                                        </a>
                                    </td>
                                    <td>{{ $school->ward_no ?: '-' }}</td>
                                    <td>{{ number_format($school->total_students) }}</td>
                                    <td>{{ $school->toilet_connection ?: '-' }}</td>
                                    <td>{{ $school->main_drinking_water_source ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No school enrollment data available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
