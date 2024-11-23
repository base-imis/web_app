<table>
    <thead>
        <tr>
            <th></th>
            <th><b>Municipality </b></th>
            </tr>
    <tr>
        <th><b>Data Framework for JMP</b></th>
    </tr>
    <tr>
    <th><b> Sub-Category</b></th>
    <th>JMP Core Indicators	</th>
    <th><b>Year</b></th>
    <th> {{$years}}</th>
    </tr>
    <tr>
        <th><b>Parameter </b></th>
        <th><b>Assessment Metric or Data Point</b></th>
        <th><b>Unit</b></th>
        <th><b>Data-Value</b></th>
    </tr>
    </thead>
    <tbody>
    @foreach($results as $result)
        <tr>
            <td>{{ $result->parameter_title }}</td>
            <td>{{ $result->assmntmtrc_dtpnt }}</td>
            <td>{{ $result->unit }}</td>
            <td>{{ $result->data_value }}</td>

        </tr>
    @endforeach
    </tbody>
</table>