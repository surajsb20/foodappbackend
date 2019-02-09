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
                <h4 class="card-title">@lang('inventory.cuisine.title')</h4>
                <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                <div class="heading-elements">
                    <ul class="list-inline mb-0">
                        <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                        <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                        <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                        <li><a href="{{ route('admin.cuisines.create') }}" class="btn btn-primary add-btn btn-darken-3">@lang('inventory.cuisine.add_cuisine')</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body collapse in">
                <div class="card-block card-dashboard table-responsive">
                    <table class="table table-striped table-bordered file-export">
                        <thead>
                            <tr>
                               <!--  <th>@lang('inventory.cuisine.sl_no')</th> -->
                                <th>@lang('inventory.cuisine.name')</th>
                                <th>@lang('inventory.cuisine.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($Cuisines as $Index => $Cuisine)
                                <tr>
                                    <!-- <td>{{$Cuisine->id}}</td> -->
                                    <td>{{$Cuisine->name}}</td>
                                    <td>
                                        @if(Setting::get('DEMO_MODE')==1)
                                        <a href="{{ route('admin.cuisines.edit', $Cuisine->id) }}" class="table-btn btn btn-icon btn-success"><i class="fa fa-pencil-square-o"></i></a>
                                        
                                        <button onclick="return confirm('Please Unlink all Shops from this cuisine');"  class="table-btn btn btn-icon btn-danger" form="resource-delete-{{ $Cuisine->id }}" ><i class="fa fa-trash-o"></i></button> 
                                        @endif
                                        <form id="resource-delete-{{ $Cuisine->id }}" action="{{ route('admin.cuisines.destroy', $Cuisine->id)}}" method="POST">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9999" class="text-center">
                                        <h4>@lang('inventory.cuisine.no_record_found')</h4>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


