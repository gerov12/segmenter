<div class="container">
   <h6><b>Estado</b></h6>
   <div class="estados">
      @php
         $unico = $checksumCalculado = $checksumCorrecto = $storageOk = true;
         $owned = ($archivo->ownedByUser($archivo->user) || $archivo->user->can('Administrar Archivos', 'Ver Archivos')) ? true : false;
      @endphp
      @if($archivo->id != $archivo->original->id)
         @php
            $unico = false;
         @endphp
         <button id="btn-ver-original" class="badge badge-pill badge-warning" data-dismiss="modal" data-info="true" data-archivo="{{$archivo->id}}" data-name="{{$archivo->nombre_original}}" data-limpiable="{{$owned}}" data-owner="{{$archivo->user->name}}"><span class="bi bi-copy" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Copia</span></button>
      @elseif ($archivo->copias_count > 1)
         @php
            $unico = false;
         @endphp
         <span class="badge badge-pill badge-primary"><span class="bi bi-file-earmark-check" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Original </span></span><br>
         <button id="btn-ver-copias" class="badge badge-pill badge-warning" data-dismiss="modal" data-info="true" data-archivo="{{$archivo->id}}" data-name="{{$archivo->nombre_original}}" data-limpiables="{{$owned}}" data-owner="{{$archivo->user->name}}"><span class="bi bi-copy" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Ver copias ({{$archivo->numCopias}})</span></button>
      @endif

      @if ($archivo->checksum_control == null)
         @php
            $checksumCalculado = false;
         @endphp
         <button id="btn-checksum" class="badge badge-pill badge-checksum" data-dismiss="modal" data-info="true" data-name="{{$archivo->nombre_original}}" data-file="{{$archivo->id}}" data-status="no_check" data-recalculable="{{$owned}}"><span class="bi bi-exclamation-triangle" style="font-size: 0.8rem; color: rgb(0, 0, 0);"> Checksum no calculado</span></button>
      @elseif (!$archivo->checksumOk)
         @php
            $checksumCorrecto = false;
         @endphp
         @if ($archivo->checksumObsoleto)
            <button id="btn-checksum" class="badge badge-pill badge-danger" data-dismiss="modal" data-info="true" data-name="{{$archivo->nombre_original}}" data-file="{{$archivo->id}}" data-status="old_check" data-recalculable="{{$owned}}"><span class="bi bi-calendar-x" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Checksum obsoleto</span></button>
         @else
            <button id="btn-checksum" class="badge badge-pill badge-danger" data-dismiss="modal" data-info="true" data-name="{{$archivo->nombre_original}}" data-file="{{$archivo->id}}" data-status="wrong_check" data-recalculable="{{$owned}}"><span class="bi bi-x-circle" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Error de checksum</span></button>
         @endif
      @endif
      

      @if (!$archivo->checkStorage())
         @php
            $storageOk = false;
         @endphp       
         <span class="badge badge-pill badge-dark"><span class="bi bi-archive" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> Problema de storage</span></span>
      @endif

      @if ($unico and $checksumCalculado and $checksumCorrecto and $storageOk)
         <span class="badge badge-pill badge-success"><span class="bi bi-check" style="font-size: 0.8rem; color: rgb(255, 255, 255);"> OK</span></span>
      @endif
   </div>
   <br>
   <table class="table table-bordered" id="tabla-info">
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
            @if ($checksumCalculado == false)
            <tr>
               <th>Checksum <i class="bi bi-exclamation-triangle" style="color:orange" title="No calculado con el nuevo método"></i></th>
               <td>{{$archivo->checksum}}</td>
            </tr>
            @elseif ($checksumCorrecto == false)
            <tr>
               <th>Checksum</th>
               <td>{{$archivo->checksum}} <span style="color:red">({{$archivo->updated_at->format('d-M-Y H:i:s')}})</span></td>
            </tr>
            <tr>
               <th>Checksum (nuevo método)</th>
               <td>{{$archivo->checksum_control->checksum}} <span style="color:red">({{$archivo->checksum_control->updated_at->format('d-M-Y H:i:s')}})</span></td>
            </tr>
            @endif
            <tr>
               <th>Tamaño</th>
               <td>{{$archivo->tamaño}}</td>
            </tr>
            <tr>
               <th>mime</th>
               <td>{{$archivo->mime}}</td>
            </tr>
            <tr>
               <th>Procesado</th>
               @if ($archivo->procesado == 1)
                  <td><i class="bi bi-check-circle-fill" style="color:green"></i></td>
               @else
                  <td><i class="bi bi-x-circle-fill" style="color:red"></i></td>
               @endif
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
            @if ($archivo->viewers->count() > 0)
               <tr>
                  <th>Observado por {{$archivo->viewers->count()}} <i class="bi bi-eye-fill" style="font-size:15px"></i></th>
                  <td>
                     <table class="table table-bordered">
                        <tbody>
                           <tr>
                              <th>Nombre</th>
                              <th>Email</th>
                              
                           </tr>
                           @foreach ($archivo->viewers as $viewer)
                              <tr>
                                 <td>{{$viewer->name}}</td>
                                 <td>{{$viewer->email}}</td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </td>
               </tr>
            @endif
      </tbody>
   </table>
</div>
