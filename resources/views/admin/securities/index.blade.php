@extends('admin.layouts.app')


@section('content')

<!-- BEGIN PAGE CONTENT BODY -->
<div class="page-content">
    <div class="container">

        <div class="col-md-12">

            @include($moduleViewName.".search")

            <div class="clearfix"></div>
            <div class="portlet box green">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-list"></i>{{ $page_title }}
                    </div>
                </div>
                <div class="portlet-body">
                        <table class="table table-bordered table-striped table-condensed flip-content" id="server-side-datatables">
                            <thead>
                                <tr>
                                   <th width="10%">ID</th>
                                   <th width="28%">Security Name</th>
                                   <th width="20%">CUSIP Name</th>
                                   <th width="13%">Market Type</th>
                                   <th width="15%">Country</th>
                                   <th width="5%">Benchmark</th>
                                   <th width="5%">Default</th>
                                   <th width="10%" data-orderable="false">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
@section('styles')

@endsection

@section('scripts')
    <script type="text/javascript">


    $(document).ready(function(){


        $("#search-frm").submit(function(){
            oTableCustom.draw();
            return false;
        });


        $.fn.dataTableExt.sErrMode = 'throw';

        var oTableCustom = $('#server-side-datatables').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                "url": "{!! route($moduleRouteText.'.data') !!}",
                "data": function ( data )
                {
                    data.search_cusip = $("#search-frm input[name='search_cusip']").val();
                    data.search_market = $("#search-frm select[name='search_market']").val();
                    data.search_status = $("#search-frm select[name='search_status']").val();
					data.search_country = $("#search-frm input[name='search_country']").val();
                    data.search_security_name = $("#search-frm input[name='search_security_name']").val();
                }
            },
            "order": [[ 0, "asc" ]],
            columns: [
                { data :'id' , name : 'id' },
                { data: 'security_name', name: 'security_name' },
                { data: 'CUSIP', name: 'CUSIP' },
                { data: 'market_name', name: '{{ TBL_MARKETS }}.market_name'},
                { data: 'country', name: '{{ TBL_COUNTRY }}.title'},
                { data: 'benchmark_family', name: 'benchmark_family' },
                { data: 'default', name: 'default' },
                { data: 'action', orderable: false, searchable: false}
            ]
        });
    });
    </script>
@endsection
