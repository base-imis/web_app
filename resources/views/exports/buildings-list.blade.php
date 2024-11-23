<table>
    <thead>
    <tr>
        <th align="right" width="20"><h1><strong>BIN</strong></h1></th>
        <th align="right" width="20"><h1><strong>House Number</strong></h1></th>
        <th align="right" width="20"><h1><strong>House Locality/Address</strong></h1></th>
        <th align="right" width="20"><h1><strong>Containment ID</strong></h1></th>
        <th align="right" width="20"><h1><strong>Tax Code/ Holding ID</strong></h1></th>
        <th align="right" width="20"><h1><strong>Ward Number</strong></h1></th>
        <th align="right" width="20"><h1><strong>Road Code</strong></h1></th>
        <th align="right" width="20"><h1><strong>Drain Code</strong></h1></th>
        <th align="right" width="20"><h1><strong>Number of Floors</strong></h1></th>
        <th align="right" width="20"><h1><strong>Structure Type</strong></h1></th>
        <th align="right" width="20"><h1><strong>Functional Use of Building</strong></h1></th>
        <th align="right" width="20"><h1><strong>Estimated Area of the Building ( ㎡ )</strong></h1></th>
        <th align="right" width="20"><h1><strong>BIN of Main Building</strong></h1></th>
        <th align="right" width="20"><h1><strong>Toilet Connection</strong></h1></th>
        <th align="right" width="20"><h1><strong>Presence of Toilet</strong></h1></th>
        <th align="right" width="20"><h1><strong>Number of Toilets</strong></h1></th>
        <th align="right" width="20"><h1><strong>Main Drinking Water Source</strong></h1></th>
        <th align="right" width="20"><h1><strong>Well in Premises</strong></h1></th>
        <th align="right" width="20"><h1><strong>Population with Private Toilet</strong></h1></th>
        <th align="right" width="20"><h1><strong>Number of Households</strong></h1></th>
        <th align="right" width="20"><h1><strong>Population of Building</strong></h1></th>
        <th align="right" width="20"><h1><strong>Owner Name</strong></h1></th>
        <th align="right" width="20"><h1><strong>Owner Gender</strong></h1></th>
        <th align="right" width="20"><h1><strong>Owner Contact Number</strong></h1></th>
    </tr>
    </thead>
    <tbody>
    @foreach($buildingResults as $building)
        <tr>
            <td>{{ $building->bin  }}</td>
            <td>{{ $building->house_number  }}</td>
            <td>{{ $building->house_locality  }}</td>
            <td>{{ $building->containment  }}</td>
            <td>{{ $building->tax_code  }}</td>
            <td>{{ $building->ward   }}</td>
            <td>{{ $building->road_code   }}</td>
            <td>{{ $building->drain_code  }}</td>
            <td>{{ $building->floor_count   }}</td>
            <td>{{ $building->structuretype  }}</td>
            <td>{{ $building->functionaluse   }}</td>
            <td>{{ $building->estimated_area  }}</td>
            <td>{{ $building->building_associated_to }}</td>
            <td>{{ $building->sanitationsystem  }}</td>
            <td>{{ $building->toilet_status }}</td>
            <td>{{ $building->toilet_count }}</td>
            <td>{{ $building->watersource }}</td>
            <td>{{ $building->well_presence_status }}</td>
            <td>{{ $building->population_with_private_toilet }}</td>
            <td>{{ $building->household_served }}</td>
            <td>{{ $building->population_served }}</td>
            <td>{{ $building->owner_name }}</td>
            <td>{{ $building->owner_gender }}</td>
            <td>{{ $building->owner_contact}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
