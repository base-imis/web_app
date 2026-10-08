CREATE OR REPLACE FUNCTION public.execute_select_build_sanisys_nd_criterias_part1()
 RETURNS TABLE(bin character varying, building_associated_to character varying, functional_use_id integer, use_category_id integer, toilet_type character varying, toilet_id integer, construction_year date, lic_id integer, toilet_status boolean, toilet_count integer, household_served integer, population_served integer, household_with_private_toilet integer, population_with_private_toilet integer, sewer_code character varying, sewer_connected_to_tp integer, drain_code character varying, drain_cover_type character varying, drain_surface_type character varying, drain_connected_to_tp integer, containment_id character varying, construction_date date, sanitation_system_id integer, containment_type_id integer, size numeric)
 LANGUAGE plpgsql
AS $function$
BEGIN
-- working rough
RETURN QUERY

	SELECT 
		b.bin, 
		b.building_associated_to,
		b.functional_use_id, 
		b.use_category_id,
		t.type as toilet_type,
		t.id as toilet_id,
		b.construction_year,
		b.lic_id,
	
		b.toilet_status, 
		b.toilet_count, 

		b.household_served, 
		b.population_served,
		b.household_with_private_toilet, 
		b.population_with_private_toilet,
		
		b.sewer_code,
		sl.treatment_plant_id as sewer_connected_to_tp,

		b.drain_code,
		dr.cover_type AS drain_cover_type,
		dr.surface_type AS drain_surface_type,
		dr.treatment_plant_id as drain_connected_to_tp,

		c.id as containment_id,
		c.construction_date,
		b.sanitation_system_id, 
		c.type_id as containment_type_id,
		c.size
	
		FROM building_info.buildings b
		LEFT JOIN building_info.build_contains bc ON b.bin = bc.bin AND bc.deleted_at IS NULL
		LEFT JOIN fsm.containments c ON c.id = bc.containment_id AND c.deleted_at IS NULL
		LEFT JOIN fsm.toilets t ON b.bin = t.bin AND b.deleted_at is NULL
		LEFT JOIN utility_info.sewers sl ON b.sewer_code = sl.code AND sl.deleted_at IS NULL
		LEFT JOIN utility_info.drains dr ON b.drain_code = dr.code AND dr.deleted_at IS NULL
		WHERE b.deleted_at is NULL
		
	;
    EXCEPTION
             WHEN others THEN
                 RAISE EXCEPTION 'Error occurred while inserting data: %', SQLERRM;
    END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.execute_select_build_sanisys_nd_criterias_part2()
 RETURNS TABLE(bin character varying, functional_use_id integer, use_category_id integer, lic_id integer, toilet_status boolean, sanitation_system_id integer, sanitation_system character varying, ct_toilet_id integer, ct_toilet_name character varying, ct_toilet_type character varying, ct_bin character varying, ct_operation_status boolean, ct_separate_facility_with_universal_design boolean, ct_toilet_count integer, ct_sanitation_system_id integer, ct_sanitation_system_type character varying, ct_containment_type_id integer, ct_containment_type character varying, ct_containment_id character varying, ct_construction_date date, ct_size numeric, ct_sewer_code character varying, ct_sewer_connected_to_tp integer, ct_drain_code character varying, ct_drain_cover_type character varying, ct_drain_surface_type character varying, ct_drain_connected_to_tp integer)
 LANGUAGE plpgsql
AS $function$
BEGIN
RETURN QUERY
	Select b.bin, 
		b.functional_use_id, b.use_category_id,
		b.lic_id, b.toilet_status, 
		b.sanitation_system_id, ss.sanitation_system, 
		a.ct_toilet_id, a.ct_toilet_name, a.ct_toilet_type, a.ct_bin, 
		a.ct_operation_status, a.ct_separate_facility_with_universal_design, a.ct_toilet_count,
		a.ct_sanitation_system_id, a.ct_sanitation_system_type, 
		a.ct_containment_type_id, a.ct_containment_type, 
		a.ct_containment_id, a.ct_construction_date, a.ct_size,
		a.ct_sewer_code,
		a.ct_sewer_connected_to_tp,
		a.ct_drain_code,
		a.ct_drain_cover_type,
		a.ct_drain_surface_type,
		a.ct_drain_connected_to_tp
	FROM (
		Select b.bin as ct_bin,
			t.id as ct_toilet_id, t.name as ct_toilet_name, 
			t.status as ct_operation_status, t.separate_facility_with_universal_design AS ct_separate_facility_with_universal_design,
			t.type as ct_toilet_type, b.toilet_count as ct_toilet_count, 
			b.sanitation_system_id as ct_sanitation_system_id, ss.sanitation_system as ct_sanitation_system_type, 
			c.type_id as ct_containment_type_id, ct.type as ct_containment_type, 
			c.id as ct_containment_id, c.construction_date as ct_construction_date, c.size as ct_size,
			b.sewer_code AS ct_sewer_code,
			sl.treatment_plant_id as ct_sewer_connected_to_tp,
			b.drain_code AS ct_drain_code,
			dr.cover_type AS ct_drain_cover_type,
			dr.surface_type AS ct_drain_surface_type,
			dr.treatment_plant_id as ct_drain_connected_to_tp
		FROM Building_info.buildings b
		LEFT JOIN fsm.toilets t ON b.bin = t.bin AND t.deleted_at is NULL
		LEFT JOIN building_info.build_contains bc ON b.bin = bc.bin AND bc.deleted_at is NULL
		LEFT JOIN fsm.containments c ON c.id = bc.containment_id AND c.deleted_at is NULL
		LEFT JOIN fsm.containment_types ct ON ct.id = c.type_id 
		Left Join building_info.sanitation_systems as ss ON ss.id=b.sanitation_system_id 
		LEFT JOIN utility_info.sewers sl ON b.sewer_code = sl.code AND sl.deleted_at IS NULL
		LEFT JOIN utility_info.drains dr ON b.drain_code = dr.code AND dr.deleted_at IS NULL
		WHERE b.functional_use_id=8 AND b.use_category_id = 34 --community toilet
		AND lower(t.type)='community toilet'
		AND t.status IS TRUE --operational
		AND b.deleted_at is NULL
	) a
	LEFT JOIN fsm.build_toilets bt ON a.ct_toilet_id=bt.toilet_id AND bt.deleted_at is NULL
	LEFT JOIN Building_info.buildings b ON b.bin=bt.bin AND b.deleted_at is NULL
	Left Join building_info.sanitation_systems as ss ON ss.id=b.sanitation_system_id
	;
	
    EXCEPTION
             WHEN others THEN
                 RAISE EXCEPTION 'Error occurred while inserting data: %', SQLERRM;
    END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.execute_select_build_sanisys_nd_criterias_part3()
 RETURNS TABLE(containment_id character varying, construction_date date, no_of_times_emptied integer, latest_application_id integer, latest_application_date date, latest_emptying_status boolean, latest_emptied_date date)
 LANGUAGE plpgsql
AS $function$
BEGIN
-- working rough
RETURN QUERY

	WITH filter_application AS(
		SELECT 
			a.containment_id, a.id as application_id, a.application_date, a.emptying_status,
			rank() OVER (partition by a.containment_id Order by a.application_date ASC) as no_of_times_emptied_rank,
			rank() OVER (partition by a.containment_id Order by a.application_date DESC) as no_of_times_emptied_latest_rank
		FROM fsm.applications a 
		WHERE deleted_at IS NULL
		AND a.emptying_status IS TRUE
	)
	SELECT
		bsc.containment_id,
		c.construction_date, 
		bsc.no_of_times_emptied_rank::integer as no_of_times_emptied,
		bsc.application_id as latest_application_id, 
		bsc.application_date as latest_application_date,
		bsc.emptying_status as latest_emptying_status,
		e.emptied_date as latest_emptied_date
	FROM filter_application bsc
	LEFT JOIN fsm.containments c ON c.id = bsc.containment_id AND c.deleted_at IS NULL
   	LEFT JOIN fsm.emptyings e ON bsc.application_id = e.application_id AND e.deleted_at IS NULL
	WHERE bsc.no_of_times_emptied_latest_rank = 1
	;
	
    EXCEPTION
             WHEN others THEN
                 RAISE EXCEPTION 'Error occurred while inserting data: %', SQLERRM;
    END;
$function$


-- ------------------------------------------------------------

