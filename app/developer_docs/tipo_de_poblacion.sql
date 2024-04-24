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

--
-- Data for Name: tipo_de_poblacion; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.tipo_de_poblacion VALUES (1, 'U', 'Urbano');
INSERT INTO public.tipo_de_poblacion VALUES (2, 'RA', 'Rural Agrupado');
INSERT INTO public.tipo_de_poblacion VALUES (3, 'RD', 'Rural Disperso');


--
-- Name: tipo_de_poblacion_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.tipo_de_poblacion_id_seq', 3, true);


--
-- PostgreSQL database dump complete
--

