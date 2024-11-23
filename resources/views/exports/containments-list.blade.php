<table>
    <thead>
    <tr>
    <th align="right" width="20"><h1><strong>Containment ID</strong></h1></th>
    <th align="right" width="20"><h1><strong>BIN</strong></h1></th>

        <th align="right" width="20"><h1><strong>Containment Type</strong></h1></th>
        <th align="right" width="20"><h1><strong>Septic Tank Standard Compliance</strong></h1></th>
        <th align="right" width="20"><h1><strong>Construction Date</strong></h1></th>
        <th align="right" width="20"><h1><strong>Containment Volume (m³)</strong></h1></th>
        <th align="right" width="20"><h1><strong>Containment Location</strong></h1></th>
        <th align="right" width="20"><h1><strong>Building Served</strong></h1></th>
        <th align="right" width="20"><h1><strong>Next Emptying Date</strong></h1></th>
        <th align="right" width="20"><h1><strong>Last Emptied Date</strong></h1></th>
        <th align="right" width="20"><h1><strong>Number of Times Emptied</strong></h1></th>
    </tr>
    </thead>
    <tbody>
    @foreach($containmentResults as $containment)
        <tr>
        <td>{{ $containment->id  }}</td>
        <td>{{ $containment->bin  }}</td>
            <td>{{ $containment->type  }}</td>
            <td>{{ $containment->septic_criteria  }}</td>
            <td>{{ $containment->construction_date  }}</td>
            <td>{{ $containment->size  }}</td>
            <td>{{ $containment->location  }}</td>
            <td>{{ $containment->buildings_served   }}</td>
            <td>{{ $containment->next_emptying_date  }}</td>
            <td>{{ $containment->last_emptied_date   }}</td>
            <td>{{ $containment->no_of_times_emptied  }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
