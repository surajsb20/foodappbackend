@extends('admin.layouts.app')

@section('content')
    <!-- File export table -->
    <div class="row file">
        <div class="col-xs-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">@lang('transporter.index.enquiry')</h4>
                    <a class="heading-elements-toggle"><i class="fa fa-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="list-inline mb-0">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                            <li><a href="{{ route('admin.transporters.create') }}"
                                   class="btn btn-primary add-btn btn-darken-3">@lang('transporter.index.add_transporter')</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="card-body collapse in">
                    <div class="card-block card-dashboard table-responsive">
                        <table class="table table-striped table-bordered file-export">
                            <thead>
                            <tr>
                                <th>@lang('transporter.index.sl_no')</th>
                                <th>@lang('transporter.index.name')</th>
                                <th>@lang('transporter.index.email')</th>
                                <th>@lang('transporter.index.address')</th>
                                <th>@lang('transporter.index.contact_details')</th>
                                <th>@lang('transporter.index.image')</th>
                                <th>@lang('transporter.index.rating')</th>
                                <th>@lang('transporter.index.tips')</th>
                                <th>@lang('transporter.index.earning')</th>
                                <th>@lang('transporter.index.status')</th>
                                <th>@lang('transporter.index.action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($Users as $key=>$User)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{$User->name}}</td>
                                    <td>
                                        @if(Setting::get('DEMO_MODE')==0)
                                            {{$User->email}}
                                        @else
                                            {{substr($User->email, 0, 1).'****'.substr($User->email, strpos($User->email, "@"))}}
                                        @endif
                                    </td>
                                    <td>{{$User->address}}</td>
                                    <td>{{$User->phone}}</td>
                                    <td>
                                        @if($User->avatar)
                                            <div class="bg-img com-img"
                                                 style="background-image: url({{ asset($User->avatar) }});"></div>
                                        @else
                                            No Image
                                        @endif
                                    </td>

                                    <td class="star">
                                        <?php for ($i = 1; $i <= $User->rating; $i++) {
                                            echo '<i class="fa fa-star" aria-hidden="true"></i>';
                                        }
                                        for ($i = 1; $i <= (5 - $User->rating); $i++) {
                                            echo '<i class="fa fa-star-o" aria-hidden="true"></i>';
                                        }
                                        ?>
                                    </td>

                                    <td> 10</td>
                                    <td> 10</td>

                                    <td>
                                        @if ($User->is_active)
                                            <a class="table-btn btn btn-icon btn-danger"
                                               href="{{ route('admin.transporter.disable', $User->id) }}">
                                                <i class="fa fa-times-circle"></i>
                                            </a>
                                        @else
                                            <a class="table-btn btn btn-icon btn-success"
                                               href="{{ route('admin.transporter.enable', $User->id) }}">
                                                <i class="fa fa-check-circle"></i>
                                            </a>
                                        @endif
                                    </td>

                                    <td>
                                        <a class="table-btn btn btn-icon btn-danger"
                                           href="{{ route('admin.transporters.docs.index', $User->id) }}">Documents
                                        </a>

                                        @if($User->status =='unsettled')
                                            <button class="table-btn btn btn-icon btn-danger"
                                                    form="resource-settle-{{ $User->id }}">Unsettle
                                            </button>

                                            <form id="resource-settle-{{ $User->id }}"
                                                  action="{{ route('admin.transporters.update', $User->id)}}"
                                                  method="POST">
                                                {{ csrf_field() }}
                                                {{ method_field('PATCH') }}
                                                <input type="hidden" name="settle" value="1"/>
                                            </form>
                                        @endif

                                        <a href="{{ route('admin.transporters.edit', $User->id) }}"
                                           class="table-btn btn btn-icon btn-success"><i
                                                    class="fa fa-pencil-square-o"></i></a>

                                        <button class="table-btn btn btn-icon btn-danger"
                                                form="resource-delete-{{ $User->id }}"><i class="fa fa-trash-o"></i>
                                        </button>

                                        <form id="resource-delete-{{ $User->id }}"
                                              action="{{ route('admin.transporters.destroy', $User->id)}}"
                                              method="POST">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                        </form>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td cols="50">No enquiry today</td>
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