--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.16
-- Dumped by pg_dump version 9.5.16

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: tipo_de_poblacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.tipo_de_poblacion (
    id integer NOT NULL,
    nombre character varying(50),
    descripcion character varying(50)
);


--
-- Name: tipo_de_poblacion_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.tipo_de_poblacion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: tipo_de_poblacion_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.tipo_de_poblacion_id_seq OWNED BY public.tipo_de_poblacion.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.tipo_de_poblacion ALTER COLUMN id SET DEFAULT nextval('public.tipo_de_poblacion_id_seq'::regclass);


--
-- PostgreSQL database dump complete
--

