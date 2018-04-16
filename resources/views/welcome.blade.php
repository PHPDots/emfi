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
			                        <h2>
			                        	{{ $slider['title'] }}
			                        </h2>
			                        <span>{{ date('F d, Y',strtotime($last_update_date)) }}</span>
			                    </div>
			                    <div class="row">
			                        <div class="col-md-6">
			                            <div class="chart_left">
			                            	<div style="display: none;" id="chart-data-{{ $slider['id'] }}">
			                            		{!! json_encode($slider['chart_data']) !!}
			                            	</div>
			                                <div class="charts-container"  id="chart-{{ $slider['id'] }}" data-id="{{ $slider['id'] }}" data-type="{{ $slider['graph_type'] }}" data-banchmark="{{ $slider['option_banchmark'] }}" data-prices="{{ $slider['option_prices'] }}"
												style="width: 100%; height: 440px"></div>
			                            </div>
			                        </div>
			                        <div class="col-md-6">
			                            <div class="text_right" style="color:white;">
			                            	{!! $slider['description'] !!}
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