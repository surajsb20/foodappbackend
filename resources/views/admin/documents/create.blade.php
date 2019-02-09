@extends('admin.layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Document</h3>
        </div>

        <div class="card-body collapse in">
            <div class="card-block">
                <form class="form-horizontal" role="form" method="POST" action="{{ route('admin.documents.store') }}"
                      enctype="multipart/form-data">

                    {{ csrf_field() }}

                    <div class="form-group row">
                        <label for="name" class="col-xs-12 col-form-label">Document Name</label>
                        <div class="col-xs-10">
                            <input class="form-control" type="text" value="{{ old('name') }}" name="name" required
                                   id="name" placeholder="Document Name">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="name" class="col-xs-12 col-form-label">Document Type</label>
                        <div class="col-xs-10">
                            <select name="type">
                                <option value="DRIVER">Driver Review</option>
                                <option value="VEHICLE">Vehicle Review</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 mb-2">
                        <a href="{{ url('admin/notice') }}" class="btn btn-warning mr-1">
                            <i class="ft-x"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check-square-o"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/multiselect/css/multi-select.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/plugins/dropify/dist/css/dropify.min.css') }}">
@endsection

@section('scripts')
    <script type="text/javascript"
            src="{{ asset('assets/admin/plugins/multiselect/js/jquery.multi-select.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/admin/plugins/dropify/dist/js/dropify.min.js') }}"></script>
    <script type="text/javascript">
        $('#categories').multiSelect({selectableOptgroup: true});
        $('.dropify').dropify();
    </script>
@endsection