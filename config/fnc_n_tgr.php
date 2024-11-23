<?php
return [
     "gridsnwardpl_buildings" => [

        "fnc_set_buildings" => "
            CREATE OR REPLACE FUNCTION fnc_set_buildings()
            RETURNS TRIGGER LANGUAGE plpgsql
            AS $$
            BEGIN
                --to set no_rcc_framed, no_load_bearing, no_wooden_mud, no_cgi_sheet to grids acc to structure_type
                UPDATE layer_info.grids SET 
                    no_build = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_rcc_framed = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND b.structure_type_id = '4' AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_load_bearing = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND b.structure_type_id = '3' AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_wooden_mud = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND b.structure_type_id = '7' AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_cgi_sheet = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), b.geom) AND b.structure_type_id = '1' AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_popsrv = ( SELECT sum(b.population_served) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), ST_centroid(b.geom)) AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_hhsrv = ( SELECT sum(b.household_served) FROM building_info.buildings b, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), ST_centroid(b.geom)) AND g.id = layer_info.grids.id AND b.deleted_at is null),
                    no_build_directly_to_sewerage_network = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g 
                                WHERE ST_Intersects(ST_Transform(g.geom, 4326), b.geom) AND g.id = layer_info.grids.id AND b.deleted_at is null AND b.sanitation_system_id = '1'),
                    no_pit_holding_tank = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g 
                                WHERE ST_Intersects(ST_Transform(g.geom, 4326), b.geom) AND g.id = layer_info.grids.id AND b.deleted_at is null AND b.sanitation_system_id = '4'),
                    no_septic_tank = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.grids g 
                                WHERE ST_Intersects(ST_Transform(g.geom, 4326), b.geom) AND g.id = layer_info.grids.id AND b.deleted_at is null AND b.sanitation_system_id = '3');

                    
                --to set no_rcc_framed, no_load_bearing, no_wooden_mud, no_cgi_sheet to wardpl acc to structure_type
                UPDATE layer_info.wards SET 
                    no_build = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_rcc_framed = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND b.structure_type_id = '4' AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_load_bearing = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND b.structure_type_id = '3' AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_wooden_mud = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND b.structure_type_id = '7' AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_cgi_sheet = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), b.geom) AND b.structure_type_id = '1' AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_popsrv = ( SELECT sum(b.population_served) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), ST_centroid(b.geom)) AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_hhsrv = ( SELECT sum(b.household_served) FROM building_info.buildings b, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), ST_centroid(b.geom)) AND w.ward = layer_info.wards.ward AND b.deleted_at is null),
                    no_build_directly_to_sewerage_network = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w 
                                WHERE ST_Intersects(ST_Transform(w.geom, 4326), b.geom) AND w.ward = layer_info.wards.ward AND b.deleted_at is null AND b.sanitation_system_id = '1'),
                    no_pit_holding_tank = ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w WHERE ST_Intersects(ST_Transform(w.geom, 4326), b.geom) AND w.ward = layer_info.wards.ward AND b.deleted_at is null AND 
                            b.sanitation_system_id = '4'),
                    no_septic_tank = ( ( SELECT count(b.bin) FROM building_info.buildings b, layer_info.wards w 
                                WHERE ST_Intersects(ST_Transform(w.geom, 4326), b.geom) AND w.ward = layer_info.wards.ward AND b.deleted_at is null AND b.sanitation_system_id = '3'));
        
   
            RETURN NULL;
            END $$;",

        "tgr_set_gridsNwardpl_buildings" =>"DROP TRIGGER IF EXISTS tgr_set_gridsNwardpl_buildings ON building_info.buildings;
            CREATE TRIGGER tgr_set_gridsNwardpl_buildings
            AFTER INSERT OR DELETE
            ON building_info.buildings   		
            FOR EACH ROW
            EXECUTE PROCEDURE fnc_set_buildings();",

    ],
    
    
    "gridsnwardpl_containments"=>[

        "fnc_set_containments"=>"
            CREATE OR REPLACE FUNCTION fnc_set_containments()
            RETURNS TRIGGER LANGUAGE plpgsql
            AS $$
            BEGIN
                --to set no of containments,no of pit containments, no of septic tank containments to grids
                UPDATE layer_info.grids SET 
                no_contain = ( SELECT count(c.id) FROM fsm.containments c, layer_info.grids g WHERE ST_Contains(ST_Transform(g.geom, 4326), c.geom) AND g.id = layer_info.grids.id AND c.deleted_at is null);

            --to set no of containments,no of pit containments, no of septic tank containments to wardpl
            UPDATE layer_info.wards SET 
                no_contain = ( SELECT count(c.id) FROM fsm.containments c, layer_info.wards w WHERE ST_Contains(ST_Transform(w.geom, 4326), c.geom) AND w.ward = layer_info.wards.ward AND c.deleted_at is null);
                
            RETURN NULL;
            END $$;",

        "tgr_set_gridsNwardpl_containments"=>"DROP TRIGGER IF EXISTS tgr_set_gridsNwardpl_containments ON fsm.containments;  
            CREATE TRIGGER tgr_set_gridsNwardpl_containments
            AFTER INSERT OR DELETE
            ON fsm.containments   		
            FOR EACH ROW
            EXECUTE PROCEDURE fnc_set_containments();",

    ],
    
        "gridsnwardpl_roadline"=>[

        "fnc_set_roadline"=>"
            CREATE OR REPLACE FUNCTION fnc_set_roadline()
            RETURNS TRIGGER LANGUAGE plpgsql
            AS $$
            BEGIN
                --to set total length of roads to grids
                UPDATE layer_info.grids 
                SET total_rdlen = ( SELECT round(CAST(sum(ST_Length(ST_TRANSFORM(ST_Intersection(r.geom,g.geom),32645))/1000) as numeric ),2) FROM utility_info.roads r, layer_info.grids g
                WHERE g.id = layer_info.grids.id AND r.deleted_at is null);
                
                --to set total length of roads to wardpl
                UPDATE layer_info.wards 
                SET total_rdlen = ( SELECT round(CAST(sum(ST_Length(ST_TRANSFORM(ST_Intersection(r.geom,w.geom),32645))/1000) as numeric ),2) FROM utility_info.roads r, layer_info.wards w
                WHERE w.ward = layer_info.wards.ward AND r.deleted_at is null);
            RETURN NULL;
            END $$;",

        "tgr_set_gridsNwardpl_roadline"=>"DROP TRIGGER IF EXISTS tgr_set_gridsNwardpl_roadline ON utility_info.roads;
            CREATE TRIGGER tgr_set_gridsNwardpl_roadline
            AFTER INSERT OR UPDATE OR DELETE
            ON utility_info.roads   		
            FOR EACH ROW
            EXECUTE PROCEDURE fnc_set_roadline();",

    ],
    "summaryforchart_landuse"=>[

        "fnc_set_landusesummary"=>"
            CREATE OR REPLACE FUNCTION fnc_set_landusesummary()
            RETURNS TRIGGER LANGUAGE plpgsql
            AS $$
            BEGIN
                CREATE MATERIALIZED VIEW IF NOT EXISTS landuse_summaryforchart as
                    with classcount as (
                        SELECT Ct.type, L.class, COUNT(C.*) AS count
                        FROM fsm.containments C
                        JOIN layer_info.landuses L ON ST_Intersects(C.geom, L.geom)
                        JOIN fsm.containment_types Ct ON C.type_id = Ct.id
                        WHERE C.deleted_at IS NULL
                        GROUP BY Ct.type, L.class
                    ),
                    totalclasscount as(
                        select count(C.*) as totalclass, L.class 
                        from fsm.containments C, layer_info.landuses L 
                        where st_intersects (C.geom, L.geom) AND C.deleted_at is null
                        group by L.class
                    )
                    SELECT classcount.class, classcount.type, classcount.count, totalclasscount.totalclass,
                            ROUND(classcount.count * 100/totalclasscount.totalclass::numeric, 2 ) as percentage_proportion
                    from classcount, totalclasscount
                    where classcount.class = totalclasscount.class
                    ORDER BY classcount.class asc;
            
                REFRESH MATERIALIZED VIEW landuse_summaryforchart;
                
            RETURN NULL;
            END $$;",

        "tgr_set_landusesummary"=>"
            DROP TRIGGER IF EXISTS tgr_set_landusesummary ON fsm.containments;
            CREATE TRIGGER tgr_set_landusesummary
            AFTER INSERT OR UPDATE OR DELETE
            ON fsm.containments   		--   on which table trigger is supposed to run
            FOR EACH ROW
            EXECUTE PROCEDURE fnc_set_landusesummary();",

        "qry_call_landusesummary"=>"
                CREATE MATERIALIZED VIEW IF NOT EXISTS landuse_summaryforchart as
                    with classcount as (
                        SELECT Ct.type, L.class, COUNT(C.*) AS count
                        FROM fsm.containments C
                        JOIN layer_info.landuses L ON ST_Intersects(C.geom, L.geom)
                        JOIN fsm.containment_types Ct ON C.type_id = Ct.id
                        WHERE C.deleted_at IS NULL
                        GROUP BY Ct.type, L.class
                    ),
                    totalclasscount as(
                        select count(C.*) as totalclass, L.class 
                        from fsm.containments C, layer_info.landuses L 
                        where st_intersects (C.geom, L.geom) AND C.deleted_at is null
                        group by L.class
                    )
                    SELECT classcount.class, classcount.type, classcount.count, totalclasscount.totalclass,
                            ROUND(classcount.count * 100/totalclasscount.totalclass::numeric, 2 ) as percentage_proportion
                    from classcount, totalclasscount
                    where classcount.class = totalclasscount.class
                    ORDER BY classcount.class asc;
            
                REFRESH MATERIALIZED VIEW landuse_summaryforchart;
        ",

    ],
    
    
    "summaryforchart_builtupperward"=>[

        "fnc_set_builtupperwardsummary"=>"
            CREATE OR REPLACE FUNCTION fnc_set_builtupperwardsummary()
            RETURNS TRIGGER LANGUAGE plpgsql
            AS $$
            BEGIN
                CREATE MATERIALIZED VIEW IF NOT EXISTS builtupperward_summaryforchart as
                    with wardcount as (
                       SELECT COUNT(C.*) AS count, Ct.type, W.ward
                        FROM fsm.containments C
                        JOIN layer_info.wards W ON ST_Intersects(C.geom, W.geom)
                        JOIN layer_info.landuses L ON ST_Intersects(C.geom, L.geom)
                        JOIN fsm.containment_types Ct ON C.type_id = Ct.id  
                        WHERE L.class = 'Builtup'
                        AND C.deleted_at IS NULL
                        GROUP BY W.ward, Ct.type
                    ),
                    totalwardcount as(
                        select count(C.*) AS totalward, W.ward from 
                            fsm.containments C, layer_info.wards W, layer_info.landuses L 
                            where st_intersects (C.geom, W.geom) 
                            and (st_intersects(C.geom,L.geom) 
                            and L.class ='Builtup') 
                            AND C.deleted_at is null
                            group by W.ward
                    )
                    SELECT wardcount.ward, wardcount.type, wardcount.count, totalwardcount.totalward,
                            ROUND(wardcount.count * 100/totalwardcount.totalward::numeric, 2 ) as percentage_proportion
                    from wardcount, totalwardcount
                    where wardcount.ward = totalwardcount.ward
                    ORDER BY wardcount.ward asc;
            
                REFRESH MATERIALIZED VIEW builtupperward_summaryforchart;
                
            RETURN NULL;
            END $$;",

        "tgr_set_builtupperwardsummary"=>"
            DROP TRIGGER IF EXISTS tgr_set_builtupperwardsummary ON fsm.containments;
            CREATE TRIGGER tgr_set_builtupperwardsummary
            AFTER INSERT OR UPDATE OR DELETE
            ON fsm.containments   		--   on which table trigger is supposed to run
            FOR EACH ROW
            EXECUTE PROCEDURE fnc_set_builtupperwardsummary();",

        "qry_call_builtupperwardsummary"=>"
                CREATE MATERIALIZED VIEW IF NOT EXISTS builtupperward_summaryforchart as
                    with wardcount as (
                       SELECT COUNT(C.*) AS count, Ct.type, W.ward
                        FROM fsm.containments C
                        JOIN layer_info.wards W ON ST_Intersects(C.geom, W.geom)
                        JOIN layer_info.landuses L ON ST_Intersects(C.geom, L.geom)
                        JOIN fsm.containment_types Ct ON C.type_id = Ct.id  
                        WHERE L.class = 'Builtup'
                        AND C.deleted_at IS NULL
                        GROUP BY W.ward, Ct.type
                    ),
                    totalwardcount as(
                        select count(C.*) AS totalward, W.ward from 
                            fsm.containments C, layer_info.wards W, layer_info.landuses L 
                            where st_intersects (C.geom, W.geom) 
                            and (st_intersects(C.geom,L.geom) 
                            and L.class ='Builtup') 
                            AND C.deleted_at is null
                            group by W.ward
                    )
                    SELECT wardcount.ward, wardcount.type, wardcount.count, totalwardcount.totalward,
                            ROUND(wardcount.count * 100/totalwardcount.totalward::numeric, 2 ) as percentage_proportion
                    from wardcount, totalwardcount
                    where wardcount.ward = totalwardcount.ward
                    ORDER BY wardcount.ward asc;
            
                REFRESH MATERIALIZED VIEW builtupperward_summaryforchart;
        ",

    ],
];