<div class="container">
   <table class="table table-bordered" id="tabla-repetidos">
      <tbody>
            <tr>
               <th>ID</th>
               <td>{{$archivo->id}}</td>
            </tr>
            <tr>
               <th>Nombre original</th>
               <td>{{$archivo->nombre_original}}</td>
            </tr>
            <tr>
               <th>Nombre</th>
               <td>{{$archivo->nombre}}</td>
            </tr>
            <tr>
               <th>Tipo</th>
               <td>{{$archivo->tipo}}</td>
            </tr>
            <tr>
               <th>Checksum</th>
               <td>{{$archivo->checksum}}</td>
            </tr>
            <tr>
               <th>Tamaño</th>
               <td>{{$archivo->size}}</td>
            </tr>
            <tr>
               <th>mime</th>
               <td>{{$archivo->mime}}</td>
            </tr>
            <tr>
               <th>Procesado</th>
               <td>{{$archivo->procesado}}</td>
            </tr>
            <tr>
               <th>Tabla</th>
               <td>{{$archivo->tabla}}</td>
            </tr>
            <tr>
               <th>epsg_def</th>
               <td>{{$archivo->epsg_def}}</td>
            </tr>
            <tr>
               <th>Fecha de creación</th>
               <td>{{$archivo->created_at->format('d-M-Y H:i:s')}}</td>
            </tr>
            <tr>
               <th>Fecha de actualización</th>
               <td>{{$archivo->updated_at->format('d-M-Y H:i:s')}}</td>
            </tr>
            <tr>
               <th>Cargado por</th>
               <td>
                  <table class="table table-bordered">
                     <tbody>
                        <tr>
                           <th>ID</th>
                           <td>{{$archivo->user->id}}</td>
                        </tr>
                        <tr>
                           <th>Nombre</th>
                           <td>{{$archivo->user->name}}</td>
                        </tr>
                        <tr>
                           <th>Email</th>
                           <td>{{$archivo->user->email}}</td>
                        </tr>
                     </tbody>
                  </table>
               </td>
            </tr>
      </tbody>
   </table>
</div>
