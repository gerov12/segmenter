--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', 'public' , false);
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: v_entidades; Type: VISTA; Schema: public; Owner: -
--

CREATE OR REPLACE VIEW public.v_entidades AS (
  SELECT max(e.id) id, e.codigo, e.nombre,
  l.nombre as nomloc, d.nombre as nomdepto, p.nombre as nomprov,
  g.poligono as geometria, min(e.fecha_desde) fecha_desde, max(e.fecha_hasta) fecha_hasta
  FROM entidades e
  JOIN localidad l ON l.id=e.localidad_id
  JOIN geometrias g ON g.id=e.geometria_id
  JOIN localidad_departamento ld ON ld.localidad_id=l.id
  JOIN departamentos d ON d.id=ld.departamento_id
  JOIN provincia p ON p.id=d.provincia_id
  GROUP BY e.codigo, e.nombre,
   nomloc, nomdepto,  nomprov,
   geometria
);

GRANT SELECT ON TABLE public.v_entidades TO geoestadistica;


--
-- PostgreSQL database dump complete
--

