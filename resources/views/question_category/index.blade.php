@extends ('orbit.master')

@section ('content')
	
    <!-- BEGIN PAGE HEADER-->
    <div class="portlet ">
        <h1 class="page-title col-xs-10"> All Question categories </h1>
        <a role="button" class="btn main-green col-xs-2 pull-right" href="/question_category/new">New Question Category</a>
        <br>
    </div>
    <!-- END PAGE HEADER-->
    
    <!-- Begin: Demo Datatable services -->
    <div class="portlet light portlet-fit portlet-datatable ">
        <div class="portlet-body">
            <div class="table-container">
                <table class="table table-striped table-bordered table-hover table-checkable" id="datatable_ajax_question_category">
                    <thead>
                        <tr role="row" class="heading">
                            <th width="5%"> Id </th>
                            <th width="10%"> Name </th>
                            <th width="5%"> </th>
                        </tr>
                    </thead>
                    <tbody> </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- End: Demo Datatable services -->
    
@endsection