@extends('user.layouts.app')

@section('styles')
    <style>
        .pac-container {
            z-index: 9999999999999999999 !important;
        }


        /* 4 IMAGES CSS */
        .column {
            float: left;
            width: 25%;
            padding: 5px;
        }

        /* Clearfix (clear floats) */
        .row::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
@stop

@section('content')
    <!-- Content ================================================== -->
    <div class="container">
        <div class="row">

            <h1></h1>
            <div class="content-block">
                <h2>About Us</h2>
                <div class="title-divider"></div>

                <h1></h1>
                <h6>DitchtheKitch brings you food delivery wherever you are Calgary. Order online or thru mobile app
                    from our top restaurants partner and take your pick from the most popular cuisines.</h6>

                <video width="500" height="340" controls>
                    <source src="{{ asset('uploads/ditchkitch.mp4') }}" type="video/mp4">
                </video>

                <h1></h1>
                <h6>DitchtheKitch is your 24/7 source of online delivery for breakfast, lunch, snacks or dinner. We
                    guarantee highest quality of delivery right at your doorsteps.</h6>

                <h1></h1>
                <h6>We satisfy your cravings.</h6>

                <h1></h1>
                <h1></h1>
                <div class="content-block">
                    <h2>How It Works</h2>
                    <div class="title-divider"></div>


                    <div class="row">
                        <div class="column">
                            <img src="{{ asset('uploads/location.png') }}" alt="Location"
                                 style="width:50%; position: center;">
                            <h6>Set Your Location</h6>
                        </div>
                        <div class="column">
                            <img src="{{ asset('uploads/store.png') }}" alt="Restaurant"
                                 style="width:50%; position: center;">
                            <h6>Pick Restaurant</h6>
                        </div>
                        <div class="column">
                            <img src="{{ asset('uploads/card.png') }}" alt="Payment"
                                 style="width:50%; position: center;">
                            <h6>Securely Pay</h6>
                        </div>
                        <div class="column">
                            <img src="{{ asset('uploads/car.png') }}" alt="Deliver"
                                 style="width:50%; position: center;">
                            <h6>DitchtheKitch Deliver</h6>
                        </div>
                    </div>


                    <p></p>
                </div>
            </div>

        </div>
    </div>
    <!-- End Content =============================================== -->
@endsection
