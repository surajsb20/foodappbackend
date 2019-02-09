@extends('user.layouts.app')

@section('content')
    <!-- Content ================================================== -->
    <div class="container">
        <div class="row">
            <img src="uploads/food2.png" alt="food">

            <div class="profile-left-col col-md-3 ">

            </div>
            <!--End col-md -->
            <div class="profile-right-col col-md-9 white_bg">


                <div class="profile-right white_bg">


                    <div class="prof-content">

                        <h3 class="prof-tit">Contact Us</h3>

                        <form action="{{ route('contact.post') }}" method="POST"
                              enctype="multipart/form-data">

                            {{ csrf_field() }}

                            <div class="form-group row">
                                <label class="col-sm-2">Name</label>
                                <div class="col-sm-5">
                                    <input type="text" name="name" class="form-control" value="">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2">Email</label>
                                <div class="col-sm-5">
                                    <input type="email" name="email" class="form-control" value="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2">Phone Number</label>
                                <div class="col-sm-5">
                                    <input type="text" name="phone" class="form-control" value="">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2">Address</label>
                                <div class="col-sm-5">
                                    <textarea name="address" class="form-control"></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2">Comment</label>
                                <div class="col-sm-5">
                                    <textarea name="comment" class="form-control"></textarea>
                                </div>
                            </div>


                            <button class="submit">Submit</button>
                        </form>
                    </div>
                </div>


            </div>
            <!-- End row -->
        </div>
    </div>
    <!-- End Content =============================================== -->
@endsection
