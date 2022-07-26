alter table provincia add column srid integer;
update provincia set srid = 8333 where codigo = '02'; -- 'Ciudad Autónoma de Buenos Aires'
update provincia set srid = 22185 where codigo = '06'; -- 'Buenos Aires'
update provincia set srid = 22183 where codigo = '10'; -- 'Catamarca'
update provincia set srid = 22184 where codigo = '14'; -- 'Córdoba'
update provincia set srid = 22186 where codigo = '18'; -- 'Corrientes'
update provincia set srid = 22185 where codigo = '22'; -- 'Chaco'
update provincia set srid = 22182 where codigo = '26'; -- 'Chubut'
update provincia set srid = 22185 where codigo = '30'; -- 'Entre Ríos'
update provincia set srid = 22185 where codigo = '34'; -- 'Formosa'
update provincia set srid = 22183 where codigo = '38'; -- 'Jujuy'
update provincia set srid = 22183 where codigo = '42'; -- 'La Pampa'
update provincia set srid = 22183 where codigo = '46'; -- 'La Rioja'
update provincia set srid = 22182 where codigo = '50'; -- 'Mendoza'
update provincia set srid = 22187 where codigo = '54'; -- 'Misiones'
update provincia set srid = 22182 where codigo = '58'; -- 'Neuquén'
update provincia set srid = 22183 where codigo = '62'; -- 'Río Negro'
update provincia set srid = 22183 where codigo = '66'; -- 'Salta'
update provincia set srid = 22182 where codigo = '70'; -- 'San Juan'
update provincia set srid = 22183 where codigo = '74'; -- 'San Luis'
update provincia set srid = 22182 where codigo = '78'; -- 'Santa Cruz'
update provincia set srid = 22185 where codigo = '82'; -- 'Santa Fe'
update provincia set srid = 22184 where codigo = '86'; -- 'Santiago del Estero'
update provincia set srid = 22183 where codigo = '90'; -- 'Tucumán'
update provincia set srid = 22182 where codigo = '94'; -- 'Tierra del Fuego'
