@extends('layout')
@section('content')
<section class="home_slider">
	@if(!empty($sliders))
    <div class="owl-carousel owl-theme home_carousel">
		@foreach($sliders as $slider)
		        <div class="item">
		            <div class="home_slider_item bgcover" style="background:url({{ asset('themes/frontend/images/home-bg-1.jpg') }})">
		                <div class="container">
		                    <div class="row">
								<div class="title_belt">
			                        <h2>{{ $slider->title }}</h2>
			                        <span>{{ date('F d, Y',strtotime($last_update_date)) }}</span>
			                    </div>
			                    <div class="row">
			                        <div class="col-md-6">
			                            <div class="chart_left">
			                                <div class="charts-container" data-id="{{ $slider->security_id }}"
			                                	data-date="{{ $slider->graph_period != -1 ? date('Y-m-d', strtotime('-'.$slider->graph_period.' month')):-1 }}"
												data-period="{{ $slider->graph_period }}"
												data-title="{{ $slider->graph_title }}"
												data-type="{{ $slider->graph_type }}"
												data-country="{{ $slider->country_id }}"
												data-country_name="{{ $slider->country_name }}"
												id="chart_home_{{$slider->security_id }}" 
												style="width: 100%; height: 440px"></div>
			                            </div>
			                        </div>
			                        <div class="col-md-6">
			                            <div class="text_right" style="color:white;">
			                               {!! $slider->description !!}
			                            </div>
			                        </div>
			                    </div>
		                    </div>
		                </div>
		            </div>
		        </div>
		@endforeach
    </div>
    @endif
</section>
@stop

@section('scripts')	
	<script src="{{ asset('themes/frontend/js/home.js') }}"></script>	
@stop