@extends('admin.layouts.app')

@section('content')
 <!-- File export table -->
                <div class="row file">
                    <div class="col-xs-12">
                        <div class="card">
                            <div class="card-header">
                            @if(Setting::get('DEMO_MODE')==1)
                            <div class="col-md-12" style="height:50px;color:red;">
                                ** Demo Mode : No Permission to Edit and Delete.
                            </div>
                            @endif
                                <h4 class="card-title">NewsLetter</h4>
                                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                                <div class="heading-elements">
                                    <ul class="list-inline mb-0">
                                        <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                        <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                        <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                                       <!--  <li><a href="{{ route('admin.shops.create') }}" class="btn btn-primary add-btn btn-darken-3">@lang('shop.index.add_shop')</a></li> -->
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body collapse in">
                                <div class="card-block card-dashboard table-responsive">
                                    <table class="table table-striped table-bordered file-export">
                                        <thead>
                                            <tr>
                                                <th>@lang('shop.index.sl_no')</th>
                                                
                                                <th>@lang('shop.create.email')</th>
                                                
                                                
                                               <!--  <th>@lang('shop.index.action')</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($Newsletter as $key=>$User)
                                                <tr>
                                                    <td>{{ $key+1 }}</td>
                                                    
                                                    <td>{{ $User->email }}</td>
                                                   
                                                    
                                                    
                                                    <!--  <td>
                                                     <button class="btn btn-success">Approve</button>
                                                     <button class="btn btn-danger">Reject</button>
                                                        @if(Setting::get('PRODUCT_ADDONS')==1)
                                                            <a href="{{ route('admin.addons.index') }}?shop={{$User->id}}" class="btn btn-primary btn-darken-3 tab-order" > Addons List</a>
                                                        @endif
                                                        <a href="{{ route('admin.categories.index') }}?shop={{$User->id}}" class="btn btn-primary btn-darken-3 tab-order" > Category List</a>

                                                        <a href="{{ route('admin.products.index') }}?shop={{$User->id}}" class="btn btn-primary btn-darken-3 tab-order" > Menu List</a>

                                                        @if(Setting::get('DEMO_MODE')==1)
                                                        <a  class="table-btn btn btn-icon btn-success" href="{{ route('admin.shops.edit', $User->id) }}"><i class="fa fa-pencil-square-o"></i></a>
                                                        <button  class="table-btn btn btn-icon btn-danger" form="resource-delete-{{ $User->id }}" ><i class="fa fa-trash-o"></i></button>
                                                        @endif
                                                        <form id="resource-delete-{{ $User->id }}" action="{{ route('admin.shops.destroy', $User->id)}}" method="POST">
                                                            {{ csrf_field() }}
                                                            {{ method_field('DELETE') }}
                                                        </form>
                                                    </td> -->
                                                </tr>
                                            @empty
                                            <tr><td colspan="50">@lang('shop.index.no_record_found')</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- File export table -->


                 <!-- Menu List Modal Starts -->
    <div class="modal fade text-xs-left" id="menu-list">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h2 class="modal-title" id="myModalLabel1">Menu List</h2>
                </div>
                <div class="modal-body">
                    <div class="row m-0">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product Image</th>
                                        <th>Product Name</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">
                                            <div class="bg-img order-img" style="background-image: url(../assets/img/product-1.jpg);"></div>
                                        </th>
                                        <td>Burger Bistro</td>
                                        <td>$100</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn grey btn-outline-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Menu List Modal Ends -->
@endsection