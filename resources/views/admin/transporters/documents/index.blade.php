@extends('admin.layouts.app')

@section('content')
    <!-- File export table -->
    <div class="row file">
        <div class="col-xs-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">@lang('transporter.index.title')</h4>
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
                                <th>Document Type</th>
                                <th>Status</th>
                                <th>@lang('transporter.index.action')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($transporterDocs as $key => $transporterDoc)
                                <tr>
                                    <td>{{  $transporterDoc->id }}</td>
                                    <td>{{$transporterDoc->document->name}}</td>
                                    <td>{{$transporterDoc['status']}}</td>
                                    <td>

                                        <a href="{{ route('admin.transporters.docs.edit', $transporterDoc->id) }}"
                                           class="table-btn btn btn-icon btn-success"><i
                                                    class="fa fa-pencil-square-o"></i>
                                        </a>

                                        {{--<button class="table-btn btn btn-icon btn-danger"--}}
                                        {{--form="resource-delete-{{ $transporterDoc['id'] }}"><i--}}
                                        {{--class="fa fa-trash-o"></i>--}}
                                        {{--</button>--}}

                                        {{--<form id="resource-delete-{{ $transporterDoc['id'] }}"--}}
                                        {{--action="{{ route('admin.transporters.destroy', $transporterDoc['id'])}}"--}}
                                        {{--method="POST">--}}
                                        {{--{{ csrf_field() }}--}}
                                        {{--{{ method_field('DELETE') }}--}}
                                        {{--</form>--}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td cols="50">No document uploaded.</td>
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