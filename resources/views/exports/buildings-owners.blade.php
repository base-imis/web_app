<table>
    <thead>
    <tr>
        <th align="right" width="20"><h1><strong>BIN</strong></h1></th>
        <th align="right" width="20"><h1><strong>Tax Code/Holding ID</strong></h1></th>
        <th align="right" width="20"><h1><strong>Ward Number</strong></h1></th>
        <th align="right" width="20"><h1><strong>Road Code</strong></h1></th>
        <th align="right" width="20"><h1><strong> Presence of Toilet </strong></h1></th>
        <th align="right" width="20"><h1><strong>Number of Floors</strong></h1></th>
        <th align="right" width="20"><h1><strong>Number of Households</strong></h1></th>
        <th align="right" width="20"><h1><strong>Population of Building</strong></h1></th>
        <th align="right" width="20"><h1><strong>Structure Type</strong></h1></th>
        <th align="right" width="20"><h1><strong>Estimated Area of the Building( ㎡)</strong></h1></th>
        <th align="right" width="20"><h1><strong>Building Associated</strong></h1></th>
        <th align="right" width="20"><h1><strong>Owner Name</strong></h1></th>
        <th align="right" width="20"><h1><strong>Owner Gender</strong></h1></th>
        <th align="right" width="20"><h1><strong>Owner Contact Number</strong></h1></th>
    </tr>
    </thead>
    <tbody>
    @foreach($buildingResults as $building)
        <tr>
            <td>{{ $building->bin  }}</td>
            <td>{{ $building->tax_code  }}</td>
            <td>{{ $building->ward   }}</td>
            <td>{{ $building->road_code   }}</td>
            <td>{{ $building->toilet_status_text   }}</td>
            <td>{{ $building->floor_count   }}</td>
            <td>{{ $building->household_served   }}</td>
            <td>{{ $building->population_served   }}</td>
            <td>{{ $building->structure_type  }}</td>
            <td>{{ $building->estimated_area  }}</td>
            <td>{{ $building->building_associated_to }}</td>
            <td>{{ $building->owner_name }}</td>
            <td>{{ $building->owner_gender }}</td>
            <td>{{ $building->owner_contact }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
