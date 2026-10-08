CREATE OR REPLACE FUNCTION public.execute_select_build_sanisys_nd_criterias()
 RETURNS TABLE(bin character varying, building_associated_to character varying, functional_use_id integer, use_category_id integer, construction_year date, household_served integer, population_served integer, household_with_private_toilet integer, population_with_private_toilet integer, lic_id integer, toilet_presence_status boolean, toilet_count integer, sanitation_system_id integer, containment_type_id integer, toilet_type character varying, toilet_id integer, toilet_operation_status boolean, ct_sanitation_system_id integer, ct_containment_type_id integer, sewer_code character varying, sewer_connected_to_tp integer, drain_code character varying, drain_cover_type character varying, drain_surface_type character varying, drain_connected_to_tp integer, containment_id character varying, construction_date date, size numeric, sewer_presence_status text, drain_presence_status text, containment_presence_status text, no_of_times_emptied integer, latest_emptying_status boolean, latest_emptied_date date, safely_managed_sanitation_system text)
 LANGUAGE plpgsql
AS $function$
BEGIN
-- working rough
RETURN QUERY
	with filter_cat1 AS(
		SELECT * From execute_select_build_sanisys_nd_criterias_part1() 
	),
	filter_cat2 AS(
		SELECT * From execute_select_build_sanisys_nd_criterias_part2() 
	),
	filter_cat3 AS(
		SELECT * From execute_select_build_sanisys_nd_criterias_part3() 
	),
	filter_agg AS(
		select 
			cat1.bin,
			cat1.building_associated_to,
			cat1.functional_use_id,
			cat1.use_category_id,
			cat1.construction_year,
			cat1.household_served,
			cat1.population_served,
			cat1.household_with_private_toilet,
			cat1.population_with_private_toilet,
			cat1.lic_id,

			cat1.toilet_status as toilet_presence_status,
			cat1.toilet_count,
		
			cat1.sanitation_system_id,
			cat1.containment_type_id,
			-- COALESCE(cat1.containment_type_id, cat2.ct_containment_type_id) AS containment_type_id,

			-- for CT 
			COALESCE(cat2.ct_toilet_type, cat1.toilet_type) AS toilet_type,
			COALESCE(cat2.ct_toilet_id, cat1.toilet_id) AS toilet_id,
			COALESCE(cat2.ct_operation_status, NULL) AS toilet_operation_status,
			cat2.ct_sanitation_system_id,
			cat2.ct_containment_type_id,

			COALESCE(cat2.ct_sewer_code, cat1.sewer_code) AS sewer_code,
			COALESCE(cat2.ct_sewer_connected_to_tp, cat1.sewer_connected_to_tp) AS sewer_connected_to_tp,

			COALESCE(cat2.ct_drain_code, cat1.drain_code) AS drain_code,
			COALESCE(cat2.ct_drain_cover_type, cat1.drain_cover_type) AS drain_cover_type,
			COALESCE(cat2.ct_drain_surface_type, cat1.drain_surface_type) AS drain_surface_type,
			COALESCE(cat2.ct_drain_connected_to_tp, cat1.drain_connected_to_tp) AS drain_connected_to_tp,

			COALESCE(cat2.ct_containment_id, cat1.containment_id) AS containment_id,
			COALESCE(cat2.ct_construction_date, cat1.construction_date) AS construction_date,
			COALESCE(cat2.ct_size, cat1.size) AS size,
		
			-- sewer Connection presence status  		
			CASE WHEN cat1.sewer_code IS NOT NULL THEN 'yes' ELSE 'no' END as sewer_presence_status,
		
			-- drain Connection presence status  		
			CASE WHEN cat1.drain_code IS NOT NULL THEN 'yes' ELSE 'no' END as drain_presence_status,
		
			-- containment presence status  		
			CASE WHEN cat1.containment_id IS NOT NULL THEN 'yes' ELSE 'no' END as containment_presence_status,

			CASE WHEN cat1.containment_id IS NOT NULL THEN COALESCE(cat3.no_of_times_emptied , 0) ELSE NULL END as no_of_times_emptied,
			cat3.latest_emptying_status,
			CASE WHEN cat1.containment_id IS NOT NULL THEN cat3.latest_emptied_date ELSE NULL END as latest_emptied_date
		FROM filter_cat1 cat1 
		Left Join filter_cat2 cat2 ON cat1.bin=cat2.bin
		Left Join filter_cat3 cat3 ON cat1.containment_id=cat3.containment_id
	)
	Select agg.*,
		CASE 
			WHEN agg.sanitation_system_id NOT IN (9, 11) THEN
				CASE 
					WHEN agg.sanitation_system_id = 6 Then 'yes'
					WHEN agg.sanitation_system_id = 5 Then 'yes'
					WHEN agg.sanitation_system_id = 1 AND agg.sewer_code IS NOT NULL AND agg.sewer_connected_to_tp IS NOT NULL THEN 'yes'
					WHEN agg.sanitation_system_id = 2 AND agg.drain_code IS NOT NULL AND agg.drain_connected_to_tp IS NOT NULL THEN 'yes'
					WHEN agg.sanitation_system_id = 2 AND agg.drain_code IS NOT NULL AND lower(agg.drain_cover_type)='closed' AND lower(agg.drain_surface_type) = 'lined' THEN 'yes'
					WHEN agg.sanitation_system_id = 4 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (8,10) Then 'yes' 
					WHEN agg.sanitation_system_id = 4 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (13) AND agg.sewer_code IS NOT NULL AND agg.sewer_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.sanitation_system_id = 4 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (14) AND agg.drain_code IS NOT NULL AND agg.drain_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.sanitation_system_id = 4 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (14) AND agg.drain_code IS NOT NULL AND lower(agg.drain_cover_type)='closed' AND lower(agg.drain_surface_type) = 'lined' Then 'yes' 
					WHEN agg.sanitation_system_id = 3 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (3) Then 'yes' 
					WHEN agg.sanitation_system_id = 3 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (1) AND agg.sewer_code IS NOT NULL AND agg.sewer_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.sanitation_system_id = 3 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (2) AND agg.drain_code IS NOT NULL AND agg.drain_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.sanitation_system_id = 3 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (2) AND agg.drain_code IS NOT NULL AND lower(agg.drain_cover_type)='closed' AND lower(agg.drain_surface_type) = 'lined' Then 'yes' 
					ELSE 'no'
				END 
			-- Shared Containments
			WHEN agg.sanitation_system_id IN (11) THEN
				CASE 
					WHEN agg.sanitation_system_id = 11 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (8,10) Then 'yes' 
					WHEN agg.sanitation_system_id = 11 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (13) AND agg.sewer_code IS NOT NULL AND agg.sewer_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.sanitation_system_id = 11 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (14) AND agg.drain_code IS NOT NULL AND agg.drain_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.sanitation_system_id = 11 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (14) AND agg.drain_code IS NOT NULL AND lower(agg.drain_cover_type)='closed' AND lower(agg.drain_surface_type) = 'lined' Then 'yes' 
					WHEN agg.sanitation_system_id = 11 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (3) Then 'yes' 
					WHEN agg.sanitation_system_id = 11 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (1) AND agg.sewer_code IS NOT NULL AND agg.sewer_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.sanitation_system_id = 11 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (2) AND agg.drain_code IS NOT NULL AND agg.drain_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.sanitation_system_id = 11 AND agg.containment_id IS NOT NULL AND agg.containment_type_id IN (2) AND agg.drain_code IS NOT NULL AND lower(agg.drain_cover_type)='closed' AND lower(agg.drain_surface_type) = 'lined' Then 'yes' 
					ELSE 'no'
				END 
			-- Community Toilet
			WHEN agg.sanitation_system_id IN (9) THEN
				CASE 
					WHEN agg.ct_sanitation_system_id = 6 Then 'yes'
					WHEN agg.ct_sanitation_system_id = 5 Then 'yes'
					WHEN agg.ct_sanitation_system_id = 1 AND agg.sewer_code IS NOT NULL AND agg.sewer_connected_to_tp IS NOT NULL THEN 'yes'
					WHEN agg.ct_sanitation_system_id = 2 AND agg.drain_code IS NOT NULL AND agg.drain_connected_to_tp IS NOT NULL THEN 'yes'
					WHEN agg.ct_sanitation_system_id = 2 AND agg.drain_code IS NOT NULL AND lower(agg.drain_cover_type)='closed' AND lower(agg.drain_surface_type) = 'lined' THEN 'yes'
					WHEN agg.ct_sanitation_system_id = 4 AND agg.containment_id IS NOT NULL AND agg.ct_containment_type_id IN (8,10) Then 'yes' 
					WHEN agg.ct_sanitation_system_id = 4 AND agg.containment_id IS NOT NULL AND agg.ct_containment_type_id IN (13) AND agg.sewer_code IS NOT NULL AND agg.sewer_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.ct_sanitation_system_id = 4 AND agg.containment_id IS NOT NULL AND agg.ct_containment_type_id IN (14) AND agg.drain_code IS NOT NULL AND agg.drain_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.ct_sanitation_system_id = 4 AND agg.containment_id IS NOT NULL AND agg.ct_containment_type_id IN (14) AND agg.drain_code IS NOT NULL AND lower(agg.drain_cover_type)='closed' AND lower(agg.drain_surface_type) = 'lined' Then 'yes' 
					WHEN agg.ct_sanitation_system_id = 3 AND agg.containment_id IS NOT NULL AND agg.ct_containment_type_id IN (3) Then 'yes' 
					WHEN agg.ct_sanitation_system_id = 3 AND agg.containment_id IS NOT NULL AND agg.ct_containment_type_id IN (1) AND agg.sewer_code IS NOT NULL AND agg.sewer_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.ct_sanitation_system_id = 3 AND agg.containment_id IS NOT NULL AND agg.ct_containment_type_id IN (2) AND agg.drain_code IS NOT NULL AND agg.drain_connected_to_tp IS NOT NULL Then 'yes' 
					WHEN agg.ct_sanitation_system_id = 3 AND agg.containment_id IS NOT NULL AND agg.ct_containment_type_id IN (2) AND agg.drain_code IS NOT NULL AND lower(agg.drain_cover_type)='closed' AND lower(agg.drain_surface_type) = 'lined' Then 'yes' 
					ELSE 'no'
				END 
			END as safely_managed_sanitation_system
	FROM filter_agg agg 
	;
     EXCEPTION
             WHEN others THEN
                 RAISE EXCEPTION 'Error occurred while inserting data: %', SQLERRM;
    END;
$function$

