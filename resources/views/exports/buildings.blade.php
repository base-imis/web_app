<table>
    <thead>
    <tr>
        <th align="right" width="20"><h1><strong>Structure Type</strong></h1></th>
        <th align="right" width="20"><h1><strong>Buildings</strong></h1></th>
        <th align="right" width="20"><h1><strong>Sewer Network</strong></h1></th>
        <th align="right" width="20"><h1><strong>Drain Network</strong></h1></th>
        <th align="right" width="20"><h1><strong>Septic Tank</strong></h1></th>
        <th align="right" width="20"><h1><strong>Pit / Holding Tank</strong></h1></th>
        <th align="right" width="20"><h1><strong>Onsite Treatment</strong></h1></th>
        <th align="right" width="20"><h1><strong>Composting Toilet</strong></h1></th>
        <th align="right" width="20"><h1><strong>Water Body</strong></h1></th>
        <th align="right" width="20"><h1><strong>Open Ground</strong></h1></th>
        <th align="right" width="20"><h1><strong>Community Toilet</strong></h1></th>
        <th align="right" width="20"><h1><strong>Open Defecation</strong></h1></th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $row)
        <tr>
            <td>{{ $row['StructureType'] }}</td>
            <td>{{ $row['Buildings'] }}</td>
            <td>{{ $row['Sewer Network'] }}</td>
            <td>{{ $row['Drain Network'] }}</td>
            <td>{{ $row['Septic Tank'] }}</td>
            <td>{{ $row['Pit / Holding Tank'] }}</td>
            <td>{{ $row['Onsite Treatment'] }}</td>
            <td>{{ $row['Composting Toilet'] }}</td>
            <td>{{ $row['Water Body'] }}</td>
            <td>{{ $row['Open Ground'] }}</td>
            <td>{{ $row['Community Toilet'] }}</td>
            <td>{{ $row['Open Defecation'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>