@extends('layouts.layouts')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb" style="font-size: clamp(0.6rem, 3.2vw, 0.8rem)">
            <li class="breadcrumb-item text-secondary"><i class="fas fa-home"></i></li>
            <li class="breadcrumb-item active" aria-current="page">Compras</li>
        </ol>
    </nav>
@endsection

@can('create_purchase')
@section('create')

<button id="abrirCierreCajaModalBtn" class="btn btn-sm btn-danger" style="font-size: clamp(0.6rem, 3vw, 0.7rem)" data-toggle="modal" data-target="#cierreCajaModal"><i class="fas fa-file-pdf"></i> Cierre de caja</button>

<a class="btn btn-sm btn-success" style="font-size: clamp(0.6rem, 3vw, 0.7rem)" href="{{ route('compras.create', $com->count() > 0 ? $com[0]->id : null) }}">
    <i class="fa fa-plus-square" style="color: white;"></i>
    
    @if($com->count() == 0)
        Registrar compra
    @else
        Continuar compra
    @endif
</a>

@endsection
@endcan

@section('content')

    @if (session()->has('exito'))
        <div class="alert alert-success alert-dismissible" style="font-size: clamp(0.6rem, 3.2vw, 0.8rem);" role="alert">
            <strong>{{ session('exito') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible" style="font-size: clamp(0.6rem, 3.2vw, 0.8rem);" role="alert">
            <strong>{{ session('error') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="mb-4">
        <table class="display table table-striped" id="example"  style="font-size: 0.6rem; width: 100%">
            <thead>
                <tr>
                    <th>Fecha hora</th>
                    <th>Documento</th>
                    <th>Fecha</th>
                    <th>Proveedor</th>
                    <th>Estado</th>
                    <th>Empleado</th>
                </tr>
            </thead>
            <tbody>
            @foreach($compras as $compra)
                <tr style="font-family: 'Nunito', sans-serif; font-size: small">
                    <td>{{ $compra->created_at }}</td>
                    <td style="text-transform: uppercase">
                    @can('details_purchase')
                        <strong>
                            <a href="{{ route('compras.facturas', ['compra'=>$compra->id]) }}">{{ $compra->docummento_compra }}</a>
                        </strong>
                    @else
                        <strong>
                            <p>{{ $compra->docummento_compra }}</p>
                        </strong>
                    @endcan
                    </td>

                    <td>
                    <strong>{{ \Carbon\Carbon::parse($compra->fecha_compra)->isoFormat('DD') }} de
                            {{ \Carbon\Carbon::parse($compra->fecha_compra)->isoFormat('MMMM') }},
                            {{ \Carbon\Carbon::parse($compra->fecha_compra)->isoFormat('YYYY') }}
                        </strong>
                    </td>
                        
                    <td>{{ $compra->proveedor->nombre_proveedor }}</td>
                        
                    @if($compra->estado_compra == 'p')
                        <td>
                            <strong class="text-success">Compra en proceso</strong>
                        </td>
                    @elseif($compra->estado_compra == 'g')
                        <td>
                            <strong class="text-danger">Compra finalizada</strong>
                        </td>
                    @endif
                        
                    <td>
                        <strong>{{ $compra->user->name }}</strong>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div id="toast" class="toast">¡Cierre de caja diario exportado con éxito!</div>
    <div id="toast_monthly" style="background-color: darkslategray;" class="toast">¡Cierre de caja mensual exportado con éxito!</div>

    <!-- Modal Cierre de caja -->
    <div class="modal fade" id="cierreCajaModal" tabindex="-1" role="dialog" aria-labelledby="cierreCajaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger" style="color: white; font-size: clamp(0.7rem, 6vw, 1rem);">
                    <p class="modal-title" id="cierreCajaModalLabel">Cierre de caja - compras</p>
                </div>
                <div class="modal-body" style="font-size: clamp(0.7rem, 6vw, 0.9rem);">
                    <div class="form-group row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <strong><i class="fas fa-calendar-check"></i> Cierre de caja (Hoy)</strong>
                                </div>
                                
                                <div class="card-body">
                                    <input style="font-size: clamp(0.7rem, 6vw, 0.8rem);" type="date" class="form-control" id="fechaCierre" name="fechaCierre" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}">
                                </div>

                                <div class="card-footer">
                                    <button style="display: flex; margin: auto; font-size: clamp(0.7rem, 6vw, 0.8rem);" type="button" class="btn btn-sm btn-danger" id="cerrarCajaBtn">
                                        Exportar a PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card">
                                <?php
                                $meses = array(
                                    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                                    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
                                );
                                ?>

                                <div class="card-header">
                                    <strong><i class="fas fa-calendar-alt"></i> Cierre de caja (Mensual)</strong>
                                </div>

                                <div class="card-body">
                                    <select style="font-size: clamp(0.7rem, 6vw, 0.8rem);" class="form-control" id="fechaCierreMensual" name="fechaCierreMensual">
                                        <?php
                                        $currentMonth = date('n'); // Obtiene el mes actual (1-12)
                                        foreach ($meses as $index => $mes) {
                                            $monthValue = $index + 1; // Valor del mes (1-12)
                                            $selected = ($monthValue == $currentMonth) ? 'selected' : '';
                                            $disabled = ($monthValue > $currentMonth) ? 'disabled' : ''; // Deshabilita los meses futuros
                                            echo "<option value='$monthValue' $selected $disabled>$mes</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="card-footer">
                                    <button style="display: flex; margin: auto; font-size: clamp(0.7rem, 6vw, 0.8rem);" type="button" class="btn btn-sm btn-danger" id="cerrarCajaMensualBtn" onclick="exportarPDF()">
                                        Exportar a PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
    .toast {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        display: none; /* Oculto por defecto */
        transition: opacity 0.5s ease;
        opacity: 0;
    }

    .toast.show {
        display: block;
        opacity: 1;
    }
    </style>

    @include('layouts.datatables')

    <script>
        document.getElementById('cerrarCajaBtn').addEventListener('click', function() {
            let fecha = document.getElementById('fechaCierre').value;

            // Redirigir al servidor para generar la factura con las ventas de esa fecha
            window.location.href = '/generar-factura-compras?fecha=' + fecha;
        });
    </script>

    <script>
        function exportarPDF() {
            const mesSeleccionado = document.getElementById('fechaCierreMensual').value;
            const url = `/generar-factura-mes-actual-compras?fechaCierreMensual=${mesSeleccionado}`;
            window.location.href = url;
        }
    </script>

    <script>
        // Obtener la fecha actual en el formato YYYY-MM-DD
        var hoy = new Date();
        
        // Convertir la fecha al formato ISO (YYYY-MM-DD)
        var fechaHoy = hoy.getFullYear() + '-' + ('0' + (hoy.getMonth() + 1)).slice(-2) + '-' + ('0' + hoy.getDate()).slice(-2);
        
        // Asignar la fecha actual al campo de fecha
        document.getElementById('fechaCierre').value = fechaHoy;
    </script>

    <!-- Toast -->
    <script>
        document.getElementById('cerrarCajaBtn').addEventListener('click', function() {
            const toast = document.getElementById('toast');

            // Retraso de 1 segundo (1000 ms) antes de mostrar el toast
            setTimeout(function() {
                toast.classList.add('show');

                // Ocultar el toast después de 3 segundos
                setTimeout(function() {
                    toast.classList.remove('show');
                }, 4800);
            }, 1000);
        });
    </script>

    <script>
        document.getElementById('cerrarCajaMensualBtn').addEventListener('click', function() {
            const toast = document.getElementById('toast_monthly');

            // Retraso de 1 segundo (1000 ms) antes de mostrar el toast
            setTimeout(function() {
                toast.classList.add('show');

                // Ocultar el toast después de 3 segundos
                setTimeout(function() {
                    toast.classList.remove('show');
                }, 4800);
            }, 2000);
        });
    </script>

    <script src="{{ asset('dataTable_configs/dataTable_purchase.js') }}"></script>
@endsection
