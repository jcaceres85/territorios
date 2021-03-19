-- Table: public.geovisor

-- DROP TABLE public.geovisor;

CREATE TABLE public.geovisor
(
  id integer NOT NULL,
  name character varying,
  title character varying,
  url character varying,
  coord_ini character varying,
  max_extent character varying,
  zoom_ini integer,
  zoom_min integer,
  zoom_max integer,
  logo character varying,
  message_ini character varying,
  dictionary character varying,
  base_layer character varying(20),
  CONSTRAINT geovisor_pkey PRIMARY KEY (id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.geovisor
  OWNER TO postgres;

-- Table: public.geovisor_category

-- DROP TABLE public.geovisor_category;

CREATE TABLE public.geovisor_category
(
  id integer NOT NULL,
  geovisor_id integer NOT NULL,
  name character varying,
  ordr integer,
  parent_id integer,
  expanded boolean,
  CONSTRAINT geovisor_category_pk PRIMARY KEY (geovisor_id, id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.geovisor_category
  OWNER TO postgres;
-- Table: public.geovisor_layer

-- DROP TABLE public.geovisor_layer;

CREATE TABLE public.geovisor_layer
(
  id integer NOT NULL,
  geovisor_id integer NOT NULL,
  category_id integer,
  geoserver_url character varying,
  geoserver_layer_id character varying,
  geoserver_style_id character varying,
  name character varying,
  ordr character varying,
  active boolean,
  queryable boolean,
  zindex integer DEFAULT 1,
  transparency integer DEFAULT 100,
  CONSTRAINT geovisor_layer_pkey PRIMARY KEY (geovisor_id, id)
)
WITH (
  OIDS=FALSE
);
ALTER TABLE public.geovisor_layer
  OWNER TO postgres;

