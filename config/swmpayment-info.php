<?php
return [

    "fnc_create_swmpaymentstatus"=>"
        DROP FUNCTION IF EXISTS swm_info.fnc_swmpaymentstatus();
        CREATE OR REPLACE FUNCTION swm_info.fnc_swmpaymentstatus()
        Returns Boolean
        LANGUAGE plpgsql AS $$
        BEGIN
            DROP TABLE IF EXISTS swm_info.swmservice_payment_status CASCADE;
                
            CREATE TABLE swm_info.swmservice_payment_status AS
            SELECT b.swm_customer_id, b.bin as bin, b.tax_code, b.ward, b.building_associated_to, t.customer_name, t.customer_contact, t.last_payment_date,  			
                CASE 
                    WHEN t.last_payment_date='1970-01-01' THEN 99    
                    WHEN t.last_payment_date is not NULL THEN 
                        CASE
                            WHEN date_part('year', AGE(CURRENT_DATE, t.last_payment_date::date))::int > 5 THEN 5
                            ELSE date_part('year', AGE(CURRENT_DATE, t.last_payment_date::date))::int
                        END
                END as due_year, 
                b.geom, Now() as created_at, Now() as updated_at, b.deleted_at
            FROM building_info.buildings b 
            INNER join swm_info.swmservice_payments t 
            ON b.swm_customer_id=t.swm_customer_id
            WHERE b.deleted_at IS NULL;
           
            Return True
        ;
        END
        $$;
    ",

    "fnc_updonimprt_gridnward_swm"=>"DROP FUNCTION IF EXISTS swm_info.fnc_updonimprt_gridnward_swm();    
    CREATE OR REPLACE FUNCTION swm_info.fnc_updonimprt_gridnward_swm()
        Returns Boolean
        LANGUAGE plpgsql AS $$
        BEGIN
            UPDATE layer_info.wards SET swmsrvpmntprprtn = 0;
            UPDATE layer_info.wards w 
            SET swmsrvpmntprprtn = q.percentage_proportion
            FROM (
                    SELECT a.ward,  a.count, b.totalcount,
                    ROUND(a.count * 100/b.totalcount::numeric, 2 ) as percentage_proportion
                FROM ( 
                    select ward, count(*) as count
                    FROM swm_info.swmservice_payment_status  
                    WHERE due_year = 0 
                        AND building_associated_to is NULL 
                        AND deleted_at is NULL 
                    GROUP BY ward
                ) a
                JOIN ( 
                    select ward, count(*) as totalcount
                    FROM swm_info.swmservice_payment_status 
                    WHERE building_associated_to is NULL 
                        AND deleted_at is NULL 
                    GROUP BY ward
                ) b ON b.ward = a.ward
                ORDER BY a.ward asc
            ) as q
            WHERE w.ward = q.ward;
                
            UPDATE layer_info.grids SET swmsrvpmntprprtn = 0;
            UPDATE layer_info.grids g
            SET swmsrvpmntprprtn = q.percentage_proportion
            FROM (
                SELECT a.id,  a.count, b.totalcount,
                    ROUND(a.count * 100/b.totalcount::numeric, 2 ) as percentage_proportion
                FROM ( 
                    SELECT g.id, count(t.bin) as count
                    FROM swm_info.swmservice_payment_status t, layer_info.grids g
                    WHERE ST_Contains(ST_Transform(g.geom, 4326), t.geom)
                        AND t.due_year = 0 
                        AND t.building_associated_to is NULL 
                        AND t.deleted_at is NULL 
                    GROUP BY g.id
                ) a
                JOIN ( 
                    SELECT  g.id, count(t.bin) as totalcount
                    FROM swm_info.swmservice_payment_status t, layer_info.grids g
                    WHERE ST_Contains(ST_Transform(g.geom, 4326), t.geom)
                        AND t.building_associated_to is NULL 
                        AND t.deleted_at is NULL 
                    GROUP BY g.id
                ) b ON b.id = a.id
                ORDER BY a.id asc
            )as q
            WHERE g.id = q.id;
                
        Return True
    ;
    END
    $$;"

];