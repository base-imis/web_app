CREATE OR REPLACE FUNCTION public.insert_data_into_cwis_table(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
    _count INTEGER;
BEGIN
    IF _year IS NULL THEN
        RAISE EXCEPTION 'Year parameter cannot be NULL';
    END IF;

    -- Check if data for the _year already exists in data_mne
    SELECT COUNT(*)
    INTO _count
    FROM cwis.data_cwis
    WHERE year = _year;

    IF _count = 0 THEN
        -- Insert data if no records found for the given year
        BEGIN
            INSERT INTO cwis.data_cwis (
                outcome, indicator_code, label, 
                year,
                created_at
            )
            SELECT
                outcome, indicator_code, label, 
                _year,
                NOW() AS created_at
            FROM cwis.data_source
			Order by id ASC;
			
			-- Update data value for the required year
			EXECUTE 'SELECT update_data_into_cwis_table_revised_2024($1)'
			USING _year;
	
        EXCEPTION
            WHEN others THEN
                RAISE EXCEPTION 'Error occurred while inserting data: %', SQLERRM;
        END;
    ELSE
        RAISE NOTICE 'Data for year % already exists in data_cwis table', _year;
    END IF;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_revised_2024(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
    _count INTEGER;
	_average_water_consumption_lpcd Numeric := (SELECT value::numeric FROM public.site_settings WHERE name='average_water_consumption_lpcd' LIMIT 1);
	_waste_water_conversion_factor Numeric := (SELECT value::numeric FROM public.site_settings WHERE name='waste_water_conversion_factor' LIMIT 1);
	_greywater_conversion_factor_connected_to_sewer Numeric := (SELECT value::numeric FROM public.site_settings WHERE name='greywater_conversion_factor_connected_to_sewer' LIMIT 1);
	_greywater_conversion_factor_not_connected_to_sewer Numeric := (SELECT value::numeric FROM public.site_settings WHERE name='greywater_conversion_factor_not_connected_to_sewer' LIMIT 1);
	
	_fs_generation_from_containment_not_connected_to_sewer_lpcd Numeric := (SELECT value::numeric FROM public.site_settings WHERE name='fs_generation_from_containment_not_connected_to_sewer_lpcd' LIMIT 1);
	_fs_generation_from_permeable_or_unlined_pit_lpcd Numeric := (SELECT value::numeric FROM public.site_settings WHERE name='fs_generation_from_permeable_or_unlined_pit_lpcd' LIMIT 1);

	_bod_standard Numeric := (SELECT bod_standard::numeric FROM public.treatment_plant_performance_efficiency_test_settings WHERE deleted_at IS NULL LIMIT 1);
	_tss_standard Numeric := (SELECT tss_standard::numeric FROM public.treatment_plant_performance_efficiency_test_settings WHERE deleted_at IS NULL LIMIT 1);
	_ecoli_standard Numeric := (SELECT ecoli_standard::numeric FROM public.treatment_plant_performance_efficiency_test_settings WHERE deleted_at IS NULL LIMIT 1);
BEGIN
    IF _year IS NULL THEN
        RAISE EXCEPTION 'Year parameter cannot be NULL';
    END IF;

    -- Check if data for the _year already exists in data_mne
    SELECT COUNT(*)
    INTO _count
    FROM cwis.data_cwis
    WHERE year = _year;

    IF _count = 0 THEN
		RAISE NOTICE 'Data for year % doesnot exists in data_cwis table', _year;
	ELSE
        -- Update data if no records found for the given year
        BEGIN

            -- SF-1a - % of population with access to safe individual toilets
			EXECUTE 'SELECT update_data_into_cwis_table_SF_1a_newsan($1)'
			USING _year;
			
			-- SF-1b - % of IHHL OSSs that have been desludged
			EXECUTE 'SELECT update_data_into_cwis_table_SF_1b_newsan($1)'
			USING _year;
				
			-- SF-1c - % of collected FS disposed at treatment plant or designated disposal site
			EXECUTE 'SELECT update_data_into_cwis_table_SF_1c_newsan($1)'
			USING _year;
				
			-- SF-1d - FS treatment capacity as a % of total FS generated from non-sewered connections
			EXECUTE 'SELECT update_data_into_cwis_table_SF_1d_newsan($1,$2,$3)'
			USING _year, _fs_generation_from_containment_not_connected_to_sewer_lpcd,
				_fs_generation_from_permeable_or_unlined_pit_lpcd;
			
			-- SF-1e - FS treatment capacity as a % of volume disposed of at the treatment plant
			EXECUTE 'SELECT update_data_into_cwis_table_SF_1e_newsan($1)'
			USING _year;
			
			-- SF-1f - WW treatment capacity as a % of total WW generated from sewered connections and greywater and supernatant generated from non-sewered connections
			EXECUTE 'SELECT update_data_into_cwis_table_SF_1f_newsan($1,$2,$3,$4,$5)'
			USING _year, _average_water_consumption_lpcd,
				_waste_water_conversion_factor, _greywater_conversion_factor_connected_to_sewer, _greywater_conversion_factor_not_connected_to_sewer;
			
			-- SF-1g - Effectiveness of FS/WW treatment in meeting prescribed standards for effluent discharge and biosolids disposal
			EXECUTE 'SELECT update_data_into_cwis_table_SF_1g_newsan($1,$2,$3,$4)'
			USING _year, _bod_standard, _tss_standard, _ecoli_standard;

			-- SF-2a - % of low income community (LIC) population with access to safe individual toilets
			EXECUTE 'SELECT update_data_into_cwis_table_SF_2a_newsan($1)'
			USING _year;
						
			-- SF-2b - % of LIC OSSs that have been desludged
			EXECUTE 'SELECT update_data_into_cwis_table_SF_2b_newsan($1)'
			USING _year;
							
			-- SF-2c - % of FS collected from LIC that is disposed at treatment plant or designated disposal site
			EXECUTE 'SELECT update_data_into_cwis_table_SF_2c_newsan($1)'
			USING _year;

			-- SF-3 - Access to safe shared facilities
			EXECUTE 'SELECT update_data_into_cwis_table_SF_3_newsan($1)'
			USING _year;
			
			-- SF-3b - % of shared facilities that adhere to principles of universal design
			EXECUTE 'SELECT update_data_into_cwis_table_SF_3b_newsan($1)'
			USING _year;
			
			-- SF-3c - % of shared facility users who are women
			EXECUTE 'SELECT update_data_into_cwis_table_SF_3c_newsan($1)'
			USING _year;
			
			-- SF-3e - Average distance from HH to shared facility
			EXECUTE 'SELECT update_data_into_cwis_table_SF_3e_newsan($1)'
			USING _year;

			-- SF-4a - % of PTs where FS/WW generated is safely transported to TP or safely disposed in situ
			EXECUTE 'SELECT update_data_into_cwis_table_SF_4a_newsan($1)'
			USING _year;
			
			-- SF-4b - % of PTs that adhere to principles of universal design
			EXECUTE 'SELECT update_data_into_cwis_table_SF_4b_newsan($1)'
			USING _year;
			
			-- SF-4d - % of PT users who are women
			EXECUTE 'SELECT update_data_into_cwis_table_SF_4d_newsan($1)'
			USING _year;
			
			-- SF-5 - % of educational institutions where FS/WW generated is safely transported to TP or safely disposed in situ
			EXECUTE 'SELECT update_data_into_cwis_table_SF_5_newsan($1)'
			USING _year;
			
			-- SF-6 - % of healthcare facilities where FS/WW generated is safely transported to TP or safely disposed in situ
			EXECUTE 'SELECT update_data_into_cwis_table_SF_6_newsan($1)'
			USING _year;

			-- SF-7 - % of desludging services completed mechanically or semi-mechanically
			EXECUTE 'SELECT update_data_into_cwis_table_SF_7_newsan($1)'
			USING _year;

			-- SF-9 -  % of water contamination compliance (on fecal coliform)
			EXECUTE 'SELECT update_data_into_cwis_table_SF_9_newsan($1)'
			USING _year;
			
			--EQ -1 - LIC population with access to safe individual toilets / total population with access to safe individual toilets			
			EXECUTE 'SELECT update_data_into_cwis_table_EQ_1_newsan($1)'
			USING _year;


        EXCEPTION
            WHEN others THEN
                RAISE EXCEPTION 'Error occurred while inserting data: %', SQLERRM;
        END;
        
    END IF;

	
END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_eq_1_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_result_value FLOAT;

    _num_of_hhs_using_private_toilet_in_lic numeric;
	_num_of_hhs_in_lic numeric;
    _hhs_with_access_to_safe_individual_toilets numeric;
	_total_Hhs numeric;
BEGIN
    -- LIC population with access to ‘safe’ individual toilets / total population with access to safe individual toilets
	
    --  sf_2a as
        -- Number of persons with access to safe, private, individual toilets/latrines in LICs
        -- Number of persons using private toilet in LICs
        SELECT sum(population_with_private_toilet) 
            INTO _num_of_hhs_using_private_toilet_in_lic
        From execute_select_build_sanisys_nd_criterias() 
        WHERE safely_managed_sanitation_system = 'yes'
        AND lic_id IS NOT NULL
        AND EXTRACT(year from construction_year) <= _year;

        -- Total population in LICs
        SELECT sum(population_served) 
            INTO _num_of_hhs_in_lic
        From execute_select_build_sanisys_nd_criterias() 
        WHERE lic_id IS NOT NULL
        AND EXTRACT(year from construction_year) <= _year;
    
    -- sf_1a
        -- Number of population with access to safe, private, individual toilets/latrines
        -- includes sanitation with criteria defined in definition tab
        SELECT sum(population_with_private_toilet) 
            INTO _hhs_with_access_to_safe_individual_toilets
        From execute_select_build_sanisys_nd_criterias() 
        WHERE safely_managed_sanitation_system = 'yes'
        AND EXTRACT(year from construction_year) <= _year;

        -- Total number of population
        SELECT sum(population_served) 
            INTO _total_Hhs
        From execute_select_build_sanisys_nd_criterias()
        WHERE EXTRACT(year from construction_year) <= _year;
    
    SELECT round(COALESCE(
        (_num_of_hhs_using_private_toilet_in_lic / NULLIF(_num_of_hhs_in_lic, 0))/
        (_hhs_with_access_to_safe_individual_toilets / NULLIF(_total_Hhs, 0))
        , 0),3)
    INTO _result_value
    ;
    
    UPDATE cwis.data_cwis
    SET 
        data_value = _result_value::numeric,
        updated_at = NOW() 
    WHERE year = _year AND indicator_code = 'EQ-1';

    RAISE NOTICE '%: %', 'EQ-1', _result_value::numeric;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_1a_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_hhs_with_access_to_safe_individual_toilets numeric;
	_total_Hhs numeric;
	_per_of_hhs_with_access_to_safe_individual_toilets numeric;
BEGIN
	
	-- Number of population with access to safe, private, individual toilets/latrines
	-- includes sanitation with criteria defined in definition tab
	SELECT sum(population_with_private_toilet) 
 		INTO _hhs_with_access_to_safe_individual_toilets
	From execute_select_build_sanisys_nd_criterias() 
	WHERE safely_managed_sanitation_system = 'yes'
	AND EXTRACT(year from construction_year) <= _year;

	-- Total number of population
	SELECT sum(population_served) 
 		INTO _total_Hhs
	From execute_select_build_sanisys_nd_criterias()
	WHERE EXTRACT(year from construction_year) <= _year;
	
	_neumerator = _hhs_with_access_to_safe_individual_toilets;
	_denominator = _total_Hhs;

	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-1a';
			
 	RAISE NOTICE '%: %', 'SF-1a', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_1b_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_no_of_OSS_in_building_with_nonshared_toilets_desludged numeric;
	_total_OSS_in_building_with_nonshared_toilets numeric;
	_per_of_OSS_IHHL_that_have_been_desludged numeric;
BEGIN
	-- Number of IHHL OSS desludged in previous year
	-- Number of containment emptied in previous year 
	SELECT COUNT(containment_id)  
  		INTO _no_of_OSS_in_building_with_nonshared_toilets_desludged
	From execute_select_build_sanisys_nd_criterias() 
	WHERE latest_emptying_status IS TRUE 
	AND EXTRACT(year from latest_emptied_date) = _year;

	-- Number IHHL OSS in the city
	-- Total number of containment build till previous year
	SELECT COUNT(distinct containment_id)  
  		INTO _total_OSS_in_building_with_nonshared_toilets
	From execute_select_build_sanisys_nd_criterias() 
	WHERE EXTRACT(year from construction_year) <= _year;
	
	_neumerator =_no_of_OSS_in_building_with_nonshared_toilets_desludged;
	_denominator = _total_OSS_in_building_with_nonshared_toilets;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-1b';
			
 	RAISE NOTICE '%: %', 'SF-1b', _result_per;


END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_1c_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_vol_of_sludge_collected_and_disposal_at_fstp_in_year numeric;
	_vol_of_sludge_emptied_at_containment_in_year numeric;
	_per_of_collected_fs_disposed_at_tp_or_designated_disposal_site numeric;
BEGIN
	-- Volume of sludge disposed at FSTP
	-- Volume of sludge collected and reached at FSTP for disposal for given year
	SELECT sum(s.volume_of_sludge) 
  		INTO _vol_of_sludge_collected_and_disposal_at_fstp_in_year
	FROM fsm.sludge_collections s
	WHERE EXTRACT(year from s.date) = _year
	AND s.deleted_at IS NULL;

	-- Volume of sludge collected for disposal
	-- Volumn of sludge emptied at containment for given year
	SELECT sum(e.volume_of_sludge) 
  		INTO _vol_of_sludge_emptied_at_containment_in_year
	FROM fsm.emptyings e
	WHERE EXTRACT(year from e.emptied_date) = _year 
	AND e.deleted_at IS NULL;
	
	
	_neumerator = _vol_of_sludge_collected_and_disposal_at_fstp_in_year;
	_denominator = _vol_of_sludge_emptied_at_containment_in_year;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-1c';
			
 	RAISE NOTICE '%: %', 'SF-1c', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_1d_newsan(_year integer, _fs_generation_from_containment_not_connected_to_sewer_lpcd numeric, _fs_generation_from_permeable_or_unlined_pit_lpcd numeric)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_capacity_of_all_fstp_stp_per_year numeric;
	_vol_of_FS_from_containment_not_connected_to_sewer numeric;
	_vol_of_FS_from_permeable_unlined_pit numeric;
	_total_vol_of_fs_genereated numeric;
	_percentage_of_total_fs_generated_from_nss_connections numeric;

BEGIN
	-- Total capacity of all FSTPs (including STPs which can be utilised for co-treatment of FS)
	SELECT NULLIF(SUM(t.capacity_per_day), 0)::NUMERIC * 365  AS total_capacity 
		INTO _capacity_of_all_fstp_stp_per_year
	FROM fsm.treatment_plants t
	WHERE t.type::int IN (3, 4) -- FSTP OR Co-treatment
	AND status IS True --operational
	AND t.deleted_at IS NULL; 
	
	-- total volume of FS generated from population dependent on containment that are not connected to sewer
	Select sum(population_served) * ( _fs_generation_from_containment_not_connected_to_sewer_lpcd::numeric / 1000000 ) * 365 
		INTO _vol_of_FS_from_containment_not_connected_to_sewer
	From execute_select_build_sanisys_nd_criterias() 
	WHERE (
		(sanitation_system_id IN (3,4,11) AND containment_type_id IN (2,4,5,6,7,11,12,14,15,16,17))
		OR (sanitation_system_id IN (9) AND ct_containment_type_id IN (2,4,5,6,7,11,12,14,15,16,17))
	)
	AND EXTRACT(year from construction_year) <= _year;

	-- total volume of FS generated from population dependent on permeable/unlined pit
	Select  sum(population_served) * ( _fs_generation_from_permeable_or_unlined_pit_lpcd::numeric / 1000000 ) * 365 
		INTO _vol_of_FS_from_permeable_unlined_pit
	From execute_select_build_sanisys_nd_criterias() 
	WHERE (
		(sanitation_system_id IN (4) AND containment_type_id IN (9))
		OR (sanitation_system_id IN (9) AND ct_containment_type_id IN (9))
	)
	AND EXTRACT(year from construction_year) <= _year;

	_total_vol_of_fs_genereated = _vol_of_FS_from_containment_not_connected_to_sewer + _vol_of_FS_from_permeable_unlined_pit;

	_neumerator = _capacity_of_all_fstp_stp_per_year;
	_denominator = _total_vol_of_fs_genereated;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-1d';
			
	RAISE NOTICE '%: %', '_fs_generation_from_containment_not_connected_to_sewer_lpcd', _fs_generation_from_containment_not_connected_to_sewer_lpcd;
	RAISE NOTICE '%: %', '_fs_generation_from_permeable_or_unlined_pit_lpcd', _fs_generation_from_permeable_or_unlined_pit_lpcd;
 	RAISE NOTICE '%: %', 'SF-1d', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_1e_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_total_capacity_of_all_fstp_including_stp numeric;
	_total_vol_of_fs_collected numeric;
	_percentage_of_total_fs_collected_from_nss_connections numeric;

BEGIN
	-- Total capacity of all FSTPs (including STPs which can be utilised for co-treatment of FS)
	SELECT NULLIF(SUM(t.capacity_per_day), 0)::NUMERIC * 365  AS total_capacity 
		INTO _total_capacity_of_all_fstp_including_stp
	FROM fsm.treatment_plants t
	WHERE t.type::int IN (3, 4) -- FSTP OR Co-treatment
	AND status IS True -- operational
	AND t.deleted_at IS NULL;
	
	-- Total volume of FS collected
	SELECT NULLIF(sum(s.volume_of_sludge), 0)::NUMERIC  AS total_sludge
		INTO _total_vol_of_fs_collected
	FROM fsm.sludge_collections s
	WHERE EXTRACT(YEAR FROM s.date) = _year
	AND s.deleted_at IS NULL;


	_neumerator = _total_capacity_of_all_fstp_including_stp;
	_denominator = _total_vol_of_fs_collected;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-1e';
			
 	RAISE NOTICE '%: %', 'SF-1e', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_1f_newsan(_year integer, _average_water_consumption_lpcd numeric, _waste_water_conversion_factor numeric, _greywater_conversion_factor_connected_to_sewer numeric, _greywater_conversion_factor_not_connected_to_sewer numeric)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_total_capacity_of_all_wwtp Numeric;
	_vol_of_wastewater_from_ihhl_directly_connected_to_sewers Numeric;
	_vol_of_gw_and_sup_from_ihhl_with_onsite_containment_to_sewers Numeric;
	_vol_of_gw_and_sup_from_ihhl_with_onsite_containment_no_sewer Numeric;
	_vol_of_gw_from_ihhl_with_containment_pit Numeric;
	_total_capacity_available_to_treat_greywater_and_supernatant Numeric;
BEGIN
	-- CentralizedWWTP = 1;
    -- DecentralizedWWTP = 2;
    -- FSTP = 3;
    -- CoTreatmentPlant = 4;

	-- Total capacity of all waster water treatment plants (WWTPs)
	SELECT NULLIF(SUM(t.capacity_per_day), 0)::NUMERIC * 365  AS total_capacity   
		INTO _total_capacity_of_all_wwtp
	FROM fsm.treatment_plants t
	WHERE t.type::integer IN (1, 2) -- WWTP
	AND status IS True -- operational
	AND t.deleted_at IS NULL; 

	-- Total volume of wastewater generated in the city (MLD) from IHHLs directly connected to centralized/ decentralized sewers
	SELECT sum(population_served) * ( _average_water_consumption_lpcd::numeric / 1000000) * ( _waste_water_conversion_factor / 100 ) * 365
		INTO _vol_of_wastewater_from_ihhl_directly_connected_to_sewers
	From execute_select_build_sanisys_nd_criterias() 
	WHERE ( 
		sanitation_system_id = 1 -- sewer network
		OR ct_sanitation_system_id = 1 -- community toilet with sewer network 
		)
	AND EXTRACT(year from construction_year) <= _year;

	-- Total volume of greywater and supernatant generated in the city (MLD) from IHHLs connected to an onsite containment system that discharges into sewers
	SELECT sum(population_served) * ( _average_water_consumption_lpcd::numeric / 1000000) * ( _greywater_conversion_factor_connected_to_sewer::numeric/100 ) * 365
		INTO _vol_of_gw_and_sup_from_ihhl_with_onsite_containment_to_sewers
	From execute_select_build_sanisys_nd_criterias() 
	WHERE (
		(sanitation_system_id IN (3,4,11) AND containment_type_id IN (1,13)) -- containment connected to sewer
		-- sanitation of community toilets
		OR (sanitation_system_id IN (9) AND ct_sanitation_system_id IN (3,4,11) AND ct_containment_type_id IN (1,13))
	)
	AND EXTRACT(year from construction_year) <= _year;

	-- Total volume of greywater and supernatant generated from IHHLs connected to an onsite containment system that does not discharge into sewers
	SELECT sum(population_served) * ( _average_water_consumption_lpcd::numeric/ 1000000) * ( _greywater_conversion_factor_not_connected_to_sewer::numeric/100 ) * 365
		INTO _vol_of_gw_and_sup_from_ihhl_with_onsite_containment_no_sewer
	From execute_select_build_sanisys_nd_criterias() 
	WHERE (
		(sanitation_system_id IN (3,4,11) AND containment_type_id IN (8,10,14,12,11,17,16,15,2,5,3,4,7,6))
		OR sanitation_system_id IN (5,6)
		-- sanitation of community toilets
		OR (sanitation_system_id IN (9) AND ct_sanitation_system_id IN (3,4,11) AND ct_containment_type_id IN (8,10,14,12,11,17,16,15,2,5,3,4,7,6))
		OR (sanitation_system_id IN (9) AND ct_sanitation_system_id IN (5,6))
		)
	AND EXTRACT(year from construction_year) <= _year;

	-- Volume of greywater generated in the city from HHs relying on pit latrines
	SELECT sum(population_served) * ( _average_water_consumption_lpcd::numeric/ 1000000) * ( _greywater_conversion_factor_not_connected_to_sewer::numeric/100 ) * 365
		INTO _vol_of_gw_from_ihhl_with_containment_pit
	From execute_select_build_sanisys_nd_criterias() 
	WHERE (
		sanitation_system_id IN (7,8)
		OR (sanitation_system_id = 4 AND containment_type_id =9)
		-- sanitation of community toilets
		OR (sanitation_system_id = 9 AND ct_sanitation_system_id = 4 AND ct_containment_type_id =9)
	)
	AND EXTRACT(year from construction_year) <= _year;

	_neumerator = _total_capacity_of_all_wwtp;
	_denominator = round(_vol_of_wastewater_from_ihhl_directly_connected_to_sewers, 0) 
					+ round(_vol_of_gw_and_sup_from_ihhl_with_onsite_containment_to_sewers, 0)
					+ round(_vol_of_gw_and_sup_from_ihhl_with_onsite_containment_no_sewer, 0)
					+ round(_vol_of_gw_from_ihhl_with_containment_pit, 0)
					;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-1f';
			
	RAISE NOTICE '%: %', '_average_water_consumption_lpcd', _average_water_consumption_lpcd;
	RAISE NOTICE '%: %', '_waste_water_conversion_factor', _waste_water_conversion_factor;
	RAISE NOTICE '%: %', '_greywater_conversion_factor_connected_to_sewer', _greywater_conversion_factor_connected_to_sewer;
	RAISE NOTICE '%: %', '_greywater_conversion_factor_not_connected_to_sewer', _greywater_conversion_factor_not_connected_to_sewer;
	RAISE NOTICE '%: %', 'SF-1f', _result_per;
	
END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_1g_newsan(_year integer, _bod_standard numeric, _tss_standard numeric, _ecoli_standard numeric)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_num_of_samples_with_bod numeric;
	_total_num_of_effluent_samples_collected numeric;
	_effectiveness_of_fs_ww_treatment_for_effluent_discharge numeric;

	_num_of_samples_that_meet_the_guidelines_for_biosolids_disposal numeric;
	_total_num_of_biosolids_samples_collected numeric;
	_effectiveness_of_fs_ww_treatment_for_biosolids_disposal numeric;

BEGIN
	-- Number of samples that meet the guidelines for effluent discharge
	-- (BOD, ECOLI and TSS standards)
	SELECT
		COALESCE(SUM(CASE WHEN t.bod<=_bod_standard AND t.tss<=_tss_standard AND t.ecoli<=_ecoli_standard THEN 1 ELSE 0 END)::Numeric,0)
		INTO _num_of_samples_with_bod
	FROM fsm.treatmentplant_tests t
	WHERE EXTRACT(year from date) = _year;

	-- Total number of effluent samples collected
	SELECT
		COUNT(t.id)::Numeric
		INTO _total_num_of_effluent_samples_collected
	FROM fsm.treatmentplant_tests t
	WHERE EXTRACT(year from date) = _year;

	_neumerator = _num_of_samples_with_bod;
	_denominator = _total_num_of_effluent_samples_collected;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-1g';
			
 	RAISE NOTICE '%: %', 'SF-1g', _result_per;


END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_2a_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_num_of_hhs_using_private_toilet_in_lic numeric;
	_num_of_hhs_in_lic numeric;
	_per_lic_hhs_with_access_to_safe_individual_toilets numeric;
BEGIN
	-- Number of persons with access to safe, private, individual toilets/latrines in LICs
	-- Number of persons using private toilet in LICs
	SELECT sum(population_with_private_toilet) 
 		INTO _num_of_hhs_using_private_toilet_in_lic
	From execute_select_build_sanisys_nd_criterias() 
	WHERE safely_managed_sanitation_system = 'yes'
	AND lic_id IS NOT NULL
	AND EXTRACT(year from construction_year) <= _year;

	-- Total population in LICs
	SELECT sum(population_served) 
 		INTO _num_of_hhs_in_lic
	From execute_select_build_sanisys_nd_criterias() 
	WHERE lic_id IS NOT NULL
	AND EXTRACT(year from construction_year) <= _year;
	
	
	_neumerator = _num_of_hhs_using_private_toilet_in_lic;
	_denominator = _num_of_hhs_in_lic;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-2a';
			
 	RAISE NOTICE '%: %', 'SF-2a', _result_per;
				
END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_2b_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_num_of_containment_emptied_in_lics_in_previous_year numeric;
	_num_of_containment_build_in_lics_before_previous_year numeric;
	_per_of_lic_nss_ihhls_that_have_been_desludged numeric;
BEGIN
	-- Number of LICs, NSS, IHHL desludged in previous year (or given year)
	-- Number of containment emptied in LICs in previous year
	SELECT COUNT(DISTINCT containment_id) 
  		INTO _num_of_containment_emptied_in_lics_in_previous_year
	From execute_select_build_sanisys_nd_criterias() 
	WHERE latest_emptying_status IS TRUE 
	AND EXTRACT(year from latest_emptied_date) = _year
	AND sanitation_system_id IN (3,4) -- self containments only, not shared /community
	AND lic_id IS NOT NULL;

	-- Number of LICs,  IHHL NSS in the city (i.e. number of containment)
	-- Number of containment build in LICs before previous year
	SELECT COUNT(DISTINCT containment_id) 
  		INTO _num_of_containment_build_in_lics_before_previous_year
	From execute_select_build_sanisys_nd_criterias() 
	WHERE EXTRACT(year from construction_year) <= _year 
	AND sanitation_system_id IN (3,4) -- self containments only, not shared /community
	AND lic_id IS NOT NULL;
	
	
	_neumerator = _num_of_containment_emptied_in_lics_in_previous_year;
	_denominator = _num_of_containment_build_in_lics_before_previous_year;

	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-2b';
			
 	RAISE NOTICE '%: %', 'SF-2b', _result_per;
				
END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_2c_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_vol_of_sludge_collected_from_lics_disposed_at_fstp_in_year numeric;
	_vol_of_sludge_emptied_at_containment_in_lics_in_year numeric;
	_per_of_collected_fs_disposed_at_tp_or_designated_sites numeric;
BEGIN
	-- Volume of sludge disposed at FSTP collected from LICs
	-- volume of sludge collected from LICs and reached at FSTP for disposal for given year (e.g. 2023)
	SELECT sum(s.volume_of_sludge) 
  		INTO _vol_of_sludge_collected_from_lics_disposed_at_fstp_in_year
	FROM fsm.sludge_collections s
	JOIN fsm.applications a ON a.id = s.application_id
	JOIN fsm.containments c ON c.id = a.containment_id
	JOIN building_info.build_contains bc ON c.id = bc.containment_id
	JOIN building_info.buildings b ON b.bin = bc.bin
	WHERE b.lic_id IS NOT NULL
	AND EXTRACT(year from s.date) = _year
	AND s.deleted_at IS NULL;


	-- volume of sludge collected for disposal from LICs
	-- volume of sludge emptied at containment in LICs for given year (e.g. 2023)
	SELECT sum(e.volume_of_sludge) 
  		INTO _vol_of_sludge_emptied_at_containment_in_lics_in_year
	FROM fsm.emptyings e
	JOIN fsm.applications a ON a.id = e.application_id
	JOIN fsm.containments c ON c.id = a.containment_id
	JOIN building_info.build_contains bc ON c.id = bc.containment_id
	JOIN building_info.buildings b ON b.bin = bc.bin
	WHERE b.lic_id IS NOT NULL
	AND EXTRACT(year from e.emptied_date) = _year
	AND e.deleted_at IS NULL;
	
	
	_neumerator = _vol_of_sludge_collected_from_lics_disposed_at_fstp_in_year;
	_denominator = _vol_of_sludge_emptied_at_containment_in_lics_in_year;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-2c';
			
 	RAISE NOTICE '%: %', 'SF-2c', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_3_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per numeric;

	_pop_of_hhs_using_safely_managed_ct numeric;
	_total_num_of_CTs numeric;
	_per_of_dependent_pop_with_safe_access_ct numeric;
BEGIN
	-- dependent population (those without access of a private toilet/latrine) with access of safe shared facilities (CT/PT)
	-- population of household using safely managed CT
	SELECT SUM(b.population_served)::Numeric 
		INTO _pop_of_hhs_using_safely_managed_ct
	From execute_select_build_sanisys_nd_criterias() b
	JOIN fsm.build_toilets t ON t.bin=b.bin
	where b.sanitation_system_id = 9 -- dependent on Community Toilet
	AND EXTRACT(year from construction_year) <= _year
	AND t.toilet_id IN (
		Select distinct id from fsm.toilets 
		 WHERE lower(type)='community toilet'
		AND deleted_at IS NULL AND status IS TRUE
	)
	AND safely_managed_sanitation_system = 'yes'
	AND EXTRACT(year from b.construction_year) <= _year
	;
	
	-- dependent population (those without access of a private toilet/latrine)
	-- population of household using CT
	SELECT SUM(b.population_served)::Numeric 
		INTO _total_num_of_CTs
	From execute_select_build_sanisys_nd_criterias() b
	WHERE b.sanitation_system_id = 9 -- dependent on Community Toilet
	AND EXTRACT(year from b.construction_year) <= _year
	;
	
	_neumerator := _pop_of_hhs_using_safely_managed_ct;
	_denominator := _total_num_of_CTs;

	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = round(_result_per), 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-3';
	
	_per_of_dependent_pop_with_safe_access_ct = round(_result_per,2);

	RAISE NOTICE '%: %', 'SF-3', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_3b_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_no_of_CTs_with_universal_design numeric;
	_total_toilets_CT numeric;
	_per_of_CTs_with_universal_design numeric;
BEGIN
	-- Number of CTs that adhere to principles of universal design
	SELECT COUNT(t.id)::Numeric
		INTO _no_of_CTs_with_universal_design
	FROM fsm.toilets t
	WHERE lower(t.type)='community toilet' 
	AND t.status IS TRUE -- operational
	AND t.separate_facility_with_universal_design = TRUE
	AND t.deleted_at IS NULL;
	
	-- Total number of CTs  in the city
	SELECT COUNT(t.id)::Numeric
		INTO _total_toilets_CT
	FROM fsm.toilets t
	WHERE lower(t.type)='community toilet' 
	AND t.status IS TRUE -- operational
	AND t.deleted_at IS NULL;
	
	
	_neumerator = _no_of_CTs_with_universal_design;
	_denominator = _total_toilets_CT;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-3b';
			
 	RAISE NOTICE '%: %', 'SF-3b', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_3c_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_no_of_visits_to_CT_by_women numeric;
	_total_visits_to_CT numeric;
	_per_of_CT_users_who_are_women numeric;

BEGIN
	-- Number of female users in all CTs  
	SELECT SUM(female_population)::Numeric 
 		INTO _no_of_visits_to_CT_by_women
	FROM Building_info.buildings b
	WHERE b.sanitation_system_id = 9 -- dependent on Community Toilet
	AND b.deleted_at IS NULL 
	AND EXTRACT(year from b.construction_year) <= _year;

	-- Total users in all CTS
	SELECT SUM(population_served)::Numeric 
		INTO _total_visits_to_CT
	FROM Building_info.buildings b
	WHERE b.sanitation_system_id = 9 -- dependent on Community Toilet
	AND b.deleted_at IS NULL 
	AND EXTRACT(year from b.construction_year) <= _year;
	
	_neumerator = _no_of_visits_to_CT_by_women;
	_denominator = _total_visits_to_CT;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-3c';
			
 	RAISE NOTICE '%: %', 'SF-3c', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_3e_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_result_distance_m FLOAT;
BEGIN
	-- Average distance from HH to shared facility
	SELECT round(AVG(ST_Distance(ST_Transform(b.geom, 3857), ST_Transform(t.geom, 3857)))::numeric, 0) AS average_distance_meters
	FROM building_info.buildings b
	JOIN fsm.build_toilets bt ON b.bin = bt.bin 
	JOIN fsm.toilets t ON bt.toilet_id = t.id 
	WHERE b.sanitation_system_id = 9 --dependent on Community Toilet
	AND initcap(t.type) = 'Community Toilet'
	AND EXTRACT(year from b.construction_year) <= _year
	AND b.deleted_at IS NULL AND t.deleted_at IS NULL
		INTO _result_distance_m
	;
	
	UPDATE cwis.data_cwis
	SET data_value = COALESCE(round(_result_distance_m), 0), updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-3e';

	RAISE NOTICE '%: %', 'SF-3e', COALESCE(round(_result_distance_m), 0);

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_4a_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_num_of_pt_where_fs_ww_is_safely_transported_or_disposed numeric;
	_num_of_pts_in_the_city numeric;
	_per_of_pts_where_fs_ww_is_safely_transported_or_disposed numeric;
BEGIN
	-- 	Number of Pts where FS and WW generated is safely transported to TP or safely disposed in situ
	SELECT COUNT(distinct(f.bin))  
 		INTO _num_of_pt_where_fs_ww_is_safely_transported_or_disposed
	From execute_select_build_sanisys_nd_criterias() f
	LEFT JOIN fsm.toilets t ON f.bin = t.bin AND t.deleted_at is NULL
	WHERE f.functional_use_id = 8 AND f.use_category_id = 35 --public toilet
	AND f.safely_managed_sanitation_system = 'yes'
	AND t.status IS TRUE -- operational
	AND EXTRACT(year from f.construction_year) <= _year
	;
	
	-- Number of PTs in the city
	SELECT COUNT(distinct(f.bin))  
 		INTO _num_of_pts_in_the_city
	From execute_select_build_sanisys_nd_criterias() f
	LEFT JOIN fsm.toilets t ON f.bin = t.bin AND t.deleted_at is NULL
	WHERE f.functional_use_id = 8 AND f.use_category_id = 35 --public toilet
	AND t.status IS TRUE -- operational
	AND EXTRACT(year from f.construction_year) <= _year
	;
	
	
	-- _neumerator := _num_of_pt_where_fs_ww_is_safely_transported_or_disposed + _no_of_Pts_with_fs_ww_safely_disposed_in_insitu;
	_neumerator := _num_of_pt_where_fs_ww_is_safely_transported_or_disposed;
	_denominator := _num_of_pts_in_the_city;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-4a';
			
 	RAISE NOTICE '%: %', 'SF-4a', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_4b_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_no_of_PTs_with_universal_design numeric;
	_total_toilets_PT numeric;
	_per_of_PTs_with_universal_design numeric;
BEGIN
	-- Number of PTs that adhere to principles of universal design
	SELECT COUNT(t.id)::Numeric
		INTO _no_of_PTs_with_universal_design
	FROM fsm.toilets t
	WHERE lower(t.type)='public toilet' 
	AND status IS TRUE -- operational
	AND t.separate_facility_with_universal_design = TRUE
	AND t.deleted_at IS NULL;
	

	-- Total number of PTs in the city
	SELECT COUNT(t.id)::Numeric
		INTO _total_toilets_PT
	FROM fsm.toilets t
	WHERE lower(t.type)='public toilet' 
	AND status IS TRUE -- operational
	AND t.deleted_at IS NULL; 
	
	
	_neumerator = _no_of_PTs_with_universal_design;
	_denominator = _total_toilets_PT;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-4b';
			
 	RAISE NOTICE '%: %', 'SF-4b', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_4d_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_no_of_visits_to_PT_by_women numeric;
	_total_visits_to_PT numeric;
	_per_of_PT_users_who_are_women numeric;

BEGIN
	-- Number of visits by women in all PTs 
	SELECT 
		SUM(u.no_female_user)::Numeric
		INTO _no_of_visits_to_PT_by_women
	FROM fsm.ctpt_users u 
	JOIN fsm.toilets t ON u.toilet_id = t.id
	WHERE lower(t.type)='public toilet'
	AND EXTRACT(YEAR FROM u.date) = _year
	AND status IS TRUE -- operational
	AND t.deleted_at IS NULL;

	-- Total Number of visits by all in all PTs
	SELECT 
		SUM(u.no_female_user + u.no_male_user)::Numeric
		INTO _total_visits_to_PT
	FROM fsm.ctpt_users u 
	JOIN fsm.toilets t ON u.toilet_id = t.id
	WHERE lower(t.type)='public toilet' 
	AND EXTRACT(YEAR FROM u.date) = _year
	AND status IS TRUE -- operational
	AND t.deleted_at IS NULL;
	
	
	_neumerator = _no_of_visits_to_PT_by_women;
	_denominator = _total_visits_to_PT;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-4d';
			
 	RAISE NOTICE '%: %', 'SF-4d', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_5_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_no_of_education_inst_with_fs_safely_transport_or_dispose numeric;
	_total_num_of_educational_institutions numeric;
	_per_of_education_inst_with_fs_safely_transport_or_dispose numeric;
BEGIN

	-- 	Number of buildings with functional use of educational institution and safely managed sanitation system
	SELECT COUNT(functional_use_id) 
 		INTO _no_of_education_inst_with_fs_safely_transport_or_dispose
	From execute_select_build_sanisys_nd_criterias() 
	WHERE functional_use_id = 3 --educational institution
		AND safely_managed_sanitation_system = 'yes'
		AND EXTRACT(year from construction_year) <= _year;

	-- 	Total buildings with functional use of educational institution
	SELECT COUNT(functional_use_id) 
 		INTO _total_num_of_educational_institutions
	From execute_select_build_sanisys_nd_criterias() 
	WHERE functional_use_id = 3 -- educational institution
	AND EXTRACT(year from construction_year) <= _year;
	
	
	_neumerator :=_no_of_education_inst_with_fs_safely_transport_or_dispose;
	_denominator := _total_num_of_educational_institutions;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-5';
			
 	RAISE NOTICE '%: %', 'SF-5', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_6_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_no_of_healthcare_facility_with_fs_safely_transport_or_dispose numeric;
	_total_buildings_with_healthcare_facility numeric;
	_per_of_healthcare_facility_with_fs_safely_transport_or_dispose numeric;
BEGIN
	-- 	Number of buildings with functional use of health institution and safely managed sanitation system
	SELECT COUNT(functional_use_id) 
 		INTO _no_of_healthcare_facility_with_fs_safely_transport_or_dispose
	From execute_select_build_sanisys_nd_criterias() 
	WHERE functional_use_id = 4 -- health institution 
	AND safely_managed_sanitation_system = 'yes'
	AND EXTRACT(year from construction_year) <= _year;

	-- 	Total number of buildings with functional use of health institution 
	SELECT COUNT(functional_use_id) 
 		INTO _total_buildings_with_healthcare_facility
	From execute_select_build_sanisys_nd_criterias() 
	WHERE functional_use_id = 4 -- health institution 
	AND EXTRACT(year from construction_year) <= _year;
	
	_neumerator := _no_of_healthcare_facility_with_fs_safely_transport_or_dispose;
	_denominator := _total_buildings_with_healthcare_facility;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-6';
		
 	RAISE NOTICE '%: %', 'SF-6', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_7_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_no_of_desludging_carried_out_mechanically numeric;
	_per_of_desludging_carried_out_mechanically numeric;
BEGIN
	-- Number of desludging services completed mechanically or semi-mechanically in given year 
	SELECT
		COUNT(e.id)::Numeric
		INTO _no_of_desludging_carried_out_mechanically
	FROM fsm.emptyings e
	WHERE EXTRACT(year from emptied_date) = _year 
	AND e.deleted_at IS NULL;
	
	_neumerator = _no_of_desludging_carried_out_mechanically;

	-- Total number of desludging services completed in given year 
	-- Assumption: IN IMIS, every emtying is either mechanical of semi-mechanical (i.e. a=b)
	_denominator = _no_of_desludging_carried_out_mechanically;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-7';
			
 	RAISE NOTICE '%: %', 'SF-7', _result_per;

END;
$function$


-- ------------------------------------------------------------

CREATE OR REPLACE FUNCTION public.update_data_into_cwis_table_sf_9_newsan(_year integer)
 RETURNS void
 LANGUAGE plpgsql
AS $function$
DECLARE
	_neumerator numeric;
	_denominator numeric;
	_result_per text;

	_no_of_water_samples_negative_result numeric;
	_total_water_samples numeric;
	_water_contamination_compliance_on_fecal_coliform numeric;

BEGIN
	-- Number of water samples that test negative for fecal coliform in given year
	SELECT
		COUNT(ws.id)::Numeric
		INTO _no_of_water_samples_negative_result
	FROM public_health.water_samples ws
	WHERE lower(ws.water_coliform_test_result) = 'negative'
	AND EXTRACT(YEAR FROM ws.sample_date) = _year
	AND ws.deleted_at IS NULL;	
		
	-- Total number of water samples tested in given year
	SELECT
		COUNT(ws.id)::Numeric	
		INTO _total_water_samples
	FROM public_health.water_samples ws
	WHERE EXTRACT(YEAR FROM ws.sample_date) = _year
	AND ws.deleted_at IS NULL ;
	
	_neumerator = _no_of_water_samples_negative_result;
	_denominator = _total_water_samples;
	
	SELECT
	CASE
        -- Case when numerator or denominator is NULL
        WHEN _neumerator IS NULL OR _denominator IS NULL THEN 'NaN'  -- Return 'NaN' if either is NULL

        -- Case when denominator is 0
        WHEN _denominator = 0 THEN 'NaN'  -- Return 'NaN' if denominator is 0

        -- Case when both numerator and denominator are 0 (0/0 leads to indeterminate)
        WHEN _neumerator = 0 AND _denominator = 0 THEN 'NaN'  -- Return 'NaN' for 0/0

        -- Case when denominator is non-zero and valid
        ELSE round((COALESCE((_neumerator::numeric / NULLIF(_denominator, 0)), 0) * 100),0)::text  -- Standard division and percentage calculation
    END 
	INTO _result_per;
	
	UPDATE cwis.data_cwis
	SET 
		data_value = _result_per, 
		updated_at = NOW() 
	WHERE year = _year AND indicator_code = 'SF-9';
			
 	RAISE NOTICE '%: %', 'SF-9', _result_per;

END;
$function$


-- ------------------------------------------------------------

