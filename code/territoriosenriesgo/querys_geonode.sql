

--QUERY para ver si el usuario es superusuario
SELECT p.is_superuser, o.expires FROM oauth2_provider_accesstoken o INNER JOIN people_profile p ON o.user_id = p.id WHERE o.token = 'MG76Zux1nvIlcB5Sjek7l0iGeNtsJj';


--AGREGAR COLUMNA basemap a geovisor
ALTER TABLE geovisor ADD COLUMN base_layer character varying(20);
UPDATE geovisor SET base_layer='osm';




SELECT * FROM geovisor_category ORDER BY geovisor_id,id

SELECT * FROM geovisor_layer ORDER BY geovisor_id



INSERT into geovisor_category SELECT * FROM geovisor_category_resp;

INSERT into geovisor_layer SELECT * FROM geovisor_layer_resp;

DELETE FROM geovisor_layer