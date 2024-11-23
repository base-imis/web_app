<?php
// Last Modified Date: 12-04-2024
// Developed By: Innovative Solution Pvt. Ltd. (ISPL)  
return [
    // function to get point buffer buildings 
    "fnc_getPointBufferBuildings" => [

        "fnc_drop_getPointBufferBuildings" => "DROP FUNCTION IF EXISTS fnc_getPointBufferBuildings(DOUBLE PRECISION, DOUBLE PRECISION, integer);",

        "fnc_set_getPointBufferBuildings" => "
                CREATE OR REPLACE FUNCTION fnc_getPointBufferBuildings(_param_long DOUBLE PRECISION, _param_lat DOUBLE PRECISION, _param_distance integer)
                RETURNS table (structype varchar(254), count integer, sewer_network integer, drain_network integer, septic_tank integer, pit_holding_tank integer,
                onsite_treatment integer, composting_toilet integer, water_body integer, open_ground integer, community_toilet integer,
                open_defacation integer)  
                LANGUAGE plpgsql AS $$
                
                BEGIN
                    RETURN Query
                    SELECT st.type, COUNT(*)::integer AS count,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '1')::integer  AS sewer_network,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '2')::integer  AS drain_network,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '3')::integer AS septic_tank,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '4')::integer AS pit_holding_tank,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '5')::integer AS onsite_treatment,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '6')::integer AS composting_toilet,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '7')::integer AS water_body,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '8')::integer AS open_ground,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '9')::integer AS community_toilet,
                        COUNT(b.bin) filter (where b.sanitation_system_id = '10')::integer AS open_defacation
                    FROM building_info.buildings b 
                    LEFT JOIN building_info.structure_types st ON b.structure_type_id = st.id
                    LEFT JOIN building_info.sanitation_systems ss ON b.sanitation_system_id = ss.id
                    WHERE (ST_Intersects(ST_Buffer(ST_SetSRID(ST_Point(_param_long, _param_lat),4326)::GEOGRAPHY, _param_distance)::GEOMETRY, b.geom))
                    AND b.deleted_at is null
                    AND ss.map_display IS TRUE
                    GROUP BY b.structure_type_id, st.id 
                    ORDER BY st.id ASC
                ;
                END
                
                $$;",

        // "call_getPointBufferBuildings" => "Select * from fnc_getPointBufferBuildings($long, $lat, $distance);"
    ],
    

    // function to get buffer polygon buildings
    "fnc_getbufferpolygonbuildings" => [

        "fnc_drop_getbufferpolygonbuildings" => "DROP FUNCTION IF EXISTS fnc_getbufferpolygonbuildings(geometry,integer);",

        "fnc_set_getbufferpolygonbuildings" => "
        CREATE OR REPLACE FUNCTION fnc_getBufferPolygonBuildings(_param_bufferPolygonGeom geometry, _param_bufferDisancePolygon integer)
        RETURNS table (structype varchar(254), count integer,  sewer_network integer, drain_network integer, septic_tank integer, pit_holding_tank integer,
        onsite_treatment integer, composting_toilet integer, water_body integer, open_ground integer, community_toilet integer,
        open_defacation integer)  
        LANGUAGE plpgsql AS
        $$
        
        BEGIN
            RETURN Query
            SELECT st.type, COUNT(*)::integer AS count,
            COUNT(b.bin) filter (where b.sanitation_system_id = '1')::integer  AS sewer_network,
            COUNT(b.bin) filter (where b.sanitation_system_id = '2')::integer  AS drain_network,
            COUNT(b.bin) filter (where b.sanitation_system_id = '3')::integer AS septic_tank,
            COUNT(b.bin) filter (where b.sanitation_system_id = '4')::integer AS pit_holding_tank,
            COUNT(b.bin) filter (where b.sanitation_system_id = '5')::integer AS onsite_treatment,
            COUNT(b.bin) filter (where b.sanitation_system_id = '6')::integer AS composting_toilet,
            COUNT(b.bin) filter (where b.sanitation_system_id = '7')::integer AS water_body,
            COUNT(b.bin) filter (where b.sanitation_system_id = '8')::integer AS open_ground,
            COUNT(b.bin) filter (where b.sanitation_system_id = '9')::integer AS community_toilet,
            COUNT(b.bin) filter (where b.sanitation_system_id = '10')::integer AS open_defacation
        FROM building_info.buildings b 
                    LEFT JOIN building_info.structure_types st ON b.structure_type_id = st.id
                    LEFT JOIN building_info.sanitation_systems ss ON b.sanitation_system_id = ss.id

            WHERE (ST_Intersects(ST_Buffer(_param_bufferPolygonGeom::GEOGRAPHY, _param_bufferDisancePolygon)::GEOMETRY, b.geom))
            AND b.deleted_at is null 
            AND ss.map_display IS TRUE
            GROUP BY b.structure_type_id, st.id 
            ORDER BY st.id ASC
        ;
        END
        $$;",

        // "call_getbufferpolygonbuildings" => "Select * from fnc_getBufferPolygonBuildings( ST_GeomFromText("."'"."$bufferPolygonGeom"."'".",4326), $bufferDisancePolygon) ;",
    ]

];