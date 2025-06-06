@extends('admin.layouts.app')
@section('title', 'Order (Big)')

@section('css')
@endsection

@section('content')
  <!-- content -->
  <div class="content ">

    <div class="mb-4">
        <div class="row">
            <div class="col-md-10">
                <h3>Order (Big)</h3>
            </div>
            <div class="col-md-2">
                {{-- <a href="{{ route('admin.orderBigDC.create') }}" class="btn btn-primary btn-icon">
                    <i class="bi bi-plus-circle"></i> Add New Order
                </a> --}}

                <a href="{{ route('admin.assignOrderNumber') }}" class="btn btn-primary btn-icon">
                    <i class="bi bi-plus-circle"></i> Add New Order
                </a>

                
            </div>
        </div>
      
    </div>

    {{-- <div class="card">
        <div class="card-body">
            <div class="d-md-flex gap-4 align-items-center">
                <div class="d-none d-md-flex">All Orders</div>
                <div class="d-md-flex gap-4 align-items-center">
                    <form class="mb-3 mb-md-0">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <select class="form-select">
                                    <option>Sort by</option>
                                    <option value="desc">Desc</option>
                                    <option value="asc">Asc</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="30">30</option>
                                    <option value="40">40</option>
                                    <option value="50">50</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search">
                                    <button class="btn btn-outline-light" type="button">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="dropdown ms-auto">
                    <a href="#" data-bs-toggle="dropdown" class="btn btn-primary dropdown-toggle"
                        aria-haspopup="true" aria-expanded="false">Actions</a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="#" class="dropdown-item">Action</a>
                        <a href="#" class="dropdown-item">Another action</a>
                        <a href="#" class="dropdown-item">Something else here</a>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="table-responsive">
        <table class="table table-custom table-lg mb-0" id="ordersTable">
            <thead>
                <tr>
                    <th>Order#</th>
                    <th>Category</th>
                    <th>Customer Name</th>
                    <th>Customer Mobile</th>
                    <th>Order Delivery Date</th>
                    <th>Order Delivery Time</th>
                    <th>Order Nature</th>
                    <th>Order Type</th>
                    <th>Outstanding Amount</th>
                    <th>Order Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
              
            </tbody>
        </table>
    </div>

    <div id="confirmModal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #343a40; color: #fff;">
                    <h2 class="modal-title">Confirmation</h2>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <h4 align="center" style="margin: 0;">Are you sure you want to delete this ?</h4>
                </div>
                <div class="modal-footer">
                    <button type="button" id="ok_delete" name="ok_delete" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ./ content -->
@endsection

@section('js')
<script>

    $(document).ready(function() {
        var DataTable = $("#ordersTable").DataTable({
            dom: "Bfrtip",
            buttons: [{
                extend: "csv",
                className: "btn-sm"
            }],
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 20,
            ajax: {
                url: `{{route('admin.orderBigDC.index')}}`,
            },
            columns: [

                {
                    data: 'order_number',
                    name: 'order_number'
                },
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'del_date',
                    name: 'del_date'
                },
                {
                    data: 'delivery_time',
                    name: 'delivery_time'
                },
                {
                    data: 'order_nature',
                    name: 'order_nature'
                },
                {
                    data: 'order_type',
                    name: 'order_type'
                },
                {
                    data: 'outstanding_amount',
                    name: 'outstanding_amount'
                },
                {
                    data: 'orderStatus',
                    name: 'orderStatus'
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ],
            order: [[0, 'desc']],

            createdRow: function(row, data, dataIndex) {
                // Check if order_nature is 'urgent'
                if (data.order_nature == 'urgent' && data.outstanding_amount == 0) {
                    $(row).css('background-color', 'rgb(253 136 136)');
                } if(data.order_nature == 'normal' && data.outstanding_amount != 0) {
                    $(row).css('background-color', 'rgb(191 204 181)');
                } else if(data.order_nature == 'urgent' && data.outstanding_amount != 0) {
                    $(row).css('background-color', 'rgb(241 240 129)');
                }
            }

        });
        
        var delete_id;
        $(document, this).on('click', '.delete', function() {
            delete_id = $(this).data('id');
            $('#confirmModal').modal('show');
        });

        $(document).on('click', '#ok_delete', function() {
            $.ajax({
                type: "delete",
                url: "{{url('admin/orderBigDC')}}/"+delete_id,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#ok_delete').text('Deleting...');
                    $('#ok_delete').attr("disabled", true);
                },
                success: function(data) {
                    DataTable.ajax.reload();
                    $('#ok_delete').text('Delete');
                    $('#ok_delete').attr("disabled", false);
                    $('#confirmModal').modal('hide');
                    //   js_success(data);
                    if (data == 0) {
                        toastr.error("Tag Exist in Products");
                    } else if (data == 2) {
                        toastr.error("Tag Exist in Collections");
                    } else {
                        toastr.success('Record Deleted Successfully');
                    }
                }
            })
        });
    });
    </script>
@endsection