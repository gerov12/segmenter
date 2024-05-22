 CREATE OR REPLACE FUNCTION indec.insertar_geometrias(multipoligono geometry=null, multilinea geometry=null,multipunto geometry=null)
  RETURNS integer
  LANGUAGE plpgsql
 AS $function$

 DECLARE newid integer;
 begin
  INSERT INTO geometrias (multipoligono, multilinea, multipunto) VALUES ($1,$2) RETURNING id INTO newid;
  return newid;

 end;

 $function$

