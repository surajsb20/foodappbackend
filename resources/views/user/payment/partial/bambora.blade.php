<form id="payment-form" action="{{ route('card.store') }}" method="POST">
    {{ csrf_field() }}
    <div class="modal-body">
        <div class="row no-margin" id="card-payment">
            <div class="form-group col-md-12 col-sm-12">
                <label>@lang('user.card.fullname')</label>
                <input name="name" autocomplete="off" required type="text" class="form-control"
                       placeholder="@lang('user.card.fullname')">
            </div>
            <div class="form-group col-md-12 col-sm-12">
                <label>@lang('user.card.card_no')</label>
                <input name="number" type="text" onkeypress="return isNumberKey(event);" required
                       autocomplete="off" maxlength="16" class="form-control" placeholder="@lang('user.card.card_no')">
            </div>
            <div class="form-group col-md-4 col-sm-12">
                <label>@lang('user.card.month')</label>
                <input type="text" onkeypress="return isNumberKey(event);" maxlength="2" required autocomplete="off"
                       class="form-control" name="exp_month" placeholder="MM">
            </div>
            <div class="form-group col-md-4 col-sm-12">
                <label>@lang('user.card.year')</label>
                <input type="text" onkeypress="return isNumberKey(event);" maxlength="2" required autocomplete="off"
                       name="exp_year" class="form-control" placeholder="YY">
            </div>
            <div class="form-group col-md-4 col-sm-12">
                <label>@lang('user.card.cvv')</label>
                <input type="text" name="cvc" onkeypress="return isNumberKey(event);" required autocomplete="off"
                       maxlength="4" class="form-control" placeholder="@lang('user.card.cvv')">
            </div>

            <input type="hidden" name="bambora" value="bambora">
        </div>
    </div>

    <div class="modal-footer">
        <button type="submit" class="login-btn">@lang('user.card.add_card')</button>
    </div>
</form>
@section('scripts')

    {{--</script>--}}
    <script type="text/javascript">
        function isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : event.keyCode;
            if (charCode != 46 && charCode > 31
                && (charCode < 48 || charCode > 57))
                return false;

            return true;
        }

        $('#card_pay').on('change', function () {

            if ($(this).is(':checked')) {
                $('$card_id').val($(this).val());
            }
        })
    </script>
@endsection