--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.16
-- Dumped by pg_dump version 9.5.16

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', 'public' , false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: entidad; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.entidad (
    id integer NOT NULL,
    codigo character(10),
    nombre character varying(50),
    localidad_id integer NOT NULL,
    fecha_desde timestamp without time zone,
    fecha_hasta timestamp without time zone,
    observacion_id integer,
    geometria_id integer,
    cap_de_pcia integer,
    cab_de_depto integer,
    sede_gob_loc integer,
    gobierno_local_id integer
);


--
-- Name: entidad_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.entidad_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: entidad_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.entidad_id_seq OWNED BY public.entidad.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.entidad ALTER COLUMN id SET DEFAULT nextval('public.entidad_id_seq'::regclass);


--
-- PostgreSQL database dump complete
--


CREATE INDEX entidad_codigo_index
    ON public.entidad USING btree
    (codigo COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: entidad_id_index

-- DROP INDEX public.entidad_id_index;

CREATE INDEX entidad_id_index
    ON public.entidad USING btree
    (id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: entidad_localidad_id_index

-- DROP INDEX public.entidad_localidad_id_index;

CREATE INDEX entidad_localidad_id_index
    ON public.entidad USING btree
    (localidad_id ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: entidad_nombre_index

-- DROP INDEX public.entidad_nombre_index;

CREATE INDEX entidad_nombre_index
    ON public.entidad USING btree
    (nombre COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
-- Index: entidad_gobierno_local_index

-- DROP INDEX public.entidad_nombre_index;

CREATE INDEX entidad_gobierno_local_index
    ON public.entidad USING btree
    (gobierno_local_id COLLATE pg_catalog."default" ASC NULLS LAST)
    TABLESPACE pg_default;
