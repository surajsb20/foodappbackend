@extends('user.layouts.app')

@section('content')

 <!-- Content Wrapper Starts -->
        <div class="content-wrapper">
            <!-- Search Section Starts -->
            <div class="search-section">
                <div class="container">
                    <!-- Search Head Starts -->
                    <div class="search-head row">
                        <!-- Search Head Left Starts -->
                        <div class="search-head-left col-xs-10">
                            <form class="search-form">
                                <div class="input-group">
                                    <span class="input-group-addon"><button class="search-icon"><i class="ion-ios-search-strong"></i></button></span>
                                    <input type="text" name="q" value="{{Request::get('q')}}" class="form-control" placeholder="Search">
                                </div>
                            </form>
                        </div>
                        <!-- Search Head Left Ends -->
                        <!-- Search Head Right  Starts -->
                        <div class="search-head-right col-xs-2">
                            <a href="{{url(@Session::get('search_return_url'))}}" class="search-esc"><i class="ion-android-close"></i><br> <span></span> ESC</a>
                        </div>
                        <!-- Search Head Right Starts -->
                    </div>
                    <!-- Search Head Starts -->
                    @if(count($Shops)>0)
                    <!-- Search Content Starts -->
                    <div class="search-content">
                        <div>
                            <p class="related-txt">Related to <span>"{{Request::get('q')}}"</span></p>
                        </div>
                        <!-- Restaurant List Starts -->
                        <div class="restaurant-list row">
                        	@forelse($Shops as $Shop)
                        	
                            <!-- Restaurant List Box Starts -->
                            <a href="{{url('resturant/details')}}?name={{$Shop->name}}" class="food-item-box col-lg-3 col-md-4 col-sm-6 col-xs-12">
                                <div class="food-img bg-img" style="background-image: url({{$Shop->avatar}});"></div>
                                <div class="food-details">
                                    <h6 class="food-det-tit">{{$Shop->name}}</h6>
                                    <p class="food-det-txt">{{$Shop->description}}</p>
                                    <div class="food-other-details row">
                                        <div class="col-xs-3 p-r-0">
                                            <span class="food-rating"><i class="ion-android-star"></i> {{$Shop->rating}}</span>
                                        </div>
                                        <div class="col-xs-6 text-center">
                                            <span class="food-deliver-time food-list-txt">{{$Shop->estimated_delivery_time}} Mins</span>
                                        </div>
                                        <!-- <div class="col-xs-6 text-right">
                                            <span class="food-quantity-price food-list-txt">$100 for two</span>
                                        </div> -->
                                    </div>
                                </div>
                            </a>
                          
                            <!-- Restaurant List Box Starts -->
                            @empty
                            @endforelse
                            
                        </div>
                        <!-- Restaurant List Ends -->
                    </div>
                    @else
                    	@if(Request::has('q'))
	                    <div class="search-content">
	                    No Data Found!
	                    </div>
	                    @endif
                    @endif
                    <!-- Search Content Ends -->
                </div>
            </div>
            <!-- Search Section Ends -->
        </div>
        <!-- Content Wrapper Ends -->

@endsection