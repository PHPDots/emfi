<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Validator;
use Datatables;
use App\Models\HomeSlider;
use App\models\AdminLog;
use App\Models\AdminAction;
use App\Models\Securities;

class HomeSlidersController extends Controller
{
    public function __construct() {

        $this->moduleRouteText = "home-sliders";
        $this->moduleViewName = "admin.HomeSliders";
        $this->list_url = route($this->moduleRouteText.".index");

        $module = "News";
        $this->module = $module;

        $this->adminAction= new AdminAction;

        $this->modelObj = new HomeSlider();

        $this->addMsg = $module . " has been added successfully!";
        $this->updateMsg = $module . " has been updated successfully!";
        $this->deleteMsg = $module . " has been deleted successfully!";
        $this->deleteErrorMsg = $module . " can not deleted!";

        view()->share("list_url", $this->list_url);
        view()->share("moduleRouteText", $this->moduleRouteText);
        view()->share("moduleViewName", $this->moduleViewName);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_HOME_SLIDER);

        if($checkrights)
        {
            return $checkrights;
        }

        $data = array();
        $data['page_title'] = "Manage News";
		$data['countries'] = \App\Models\Country::getCountryList();
        $data['add_url'] = route($this->moduleRouteText.'.create');
        $data['btnAdd'] = \App\Models\Admin::isAccess(\App\Models\Admin::$ADD_HOME_SLIDER);
        $data['formObj'] = $this->modelObj;
        $data['action_url'] = $this->moduleRouteText.".store";
        $data['action_params'] = 0;
        $data['buttonText'] = "Save";
        $data["method"] = "POST";
        $data['months'] = getMonths();
        $data['graphs'] = \App\Models\Securities::pluck('security_name','id')->all();
        $data['orderMax'] = \App\Models\HomeSlider::getMaxOrder();
        $data['languages']= \App\Custom::getLanguages();
        $data['graphTypes']= ['line'=>'Line Graph','yield_curve'=>'Yield Curve (Scatter)', 'relval' =>  'Relval Historical Chart'];


        $data['maturities']= ['maturity'=>'Maturity','duration'=>'Duration'];
        $data['prices']= ['price'=>'Price','yield'=>'Yield','spread'=>'Spread'];
        $data['rating_orcd']= [1 => 'Rating', 0 => 'OECD'];
        $data['credit_equities']= [5 => 'Credit',1 => 'Equities'];

        $data['selected_month']         = 12;
        $data['selected_maturities']    = 'duration';
        $data['selected_prices']        = 'spread';

        if($request->get("changeID") > 0)
        {
            $news_id = $request->get("changeID");   
            $status = $request->get("changeStatus");

            $rows = \App\Models\HomeSlider::find($news_id);
            
                if($rows)
                {
                    $status = $rows->status;

                    if($status == 0)
                        $status = 1;
                    else
                        $status = 0;

                    $rows->status = $status;
                    $rows->save();

                    //store logs detail
                    $params=array();
                    $adminAction = new AdminAction();
                    
                    $params['adminuserid']  = \Auth::guard('admins')->id();
                    $params['actionid']     = $adminAction->EDIT_HOME_SLIDER;
                    $params['actionvalue']  = $news_id;
                    $params['remark']       = "Change Sataus::".$news_id;

                    $logs=\App\Models\AdminLog::writeadminlog($params);      

                    session()->flash('success_message', "Status has been changed successfully.");
                    return redirect($this->list_url);
                }
                else
                {
                    session()->flash('success_message', "Status not changed, Please try again");
                    return redirect($this->list_url);
                }

            return redirect($this->list_url);
        }

        return view($this->moduleViewName.".index", $data);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_HOME_SLIDER);

        if($checkrights)
        {
            return $checkrights;
        }

        $data = array();
        $data['formObj'] = $this->modelObj;
        $data['page_title'] = "Add ".$this->module;
        $data['action_url'] = $this->moduleRouteText.".store";
        $data['action_params'] = 0;
        $data['buttonText'] = "Save";
        $data["method"] = "POST";
		$data['months'] = getMonths();
        $data['graphs'] = \App\Models\Securities::pluck('security_name','id')->all();
        $data['countries'] = \App\Models\Country::getCountryList();
		$data['orderMax'] = \App\Models\HomeSlider::getMaxOrder();
        $data['languages']= \App\Custom::getLanguages();
        $data['graphTypes']= ['line'=>'Line Graph','yield_curve'=>'Yield Curve (Scatter)', 'relval' =>  'Relval Historical Chart',];

        $data['maturities']= ['maturity'=>'Maturity','duration'=>'Duration'];
        $data['prices']= ['price'=>'Price','yield'=>'Yield','spread'=>'Spread'];

        return view($this->moduleViewName.'.newAdd', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$ADD_HOME_SLIDER);

        if($checkrights)
        {
            return $checkrights;
        }

        $status = 1;
        $msg = $this->addMsg;
        $data = array();
		$months = getMonths();
		$months = array_keys($months);

        $graph_type = $request->get("graph_type");
        $country_id = $request->get("country_id");
        if($graph_type == "yield_curve" && (empty($country_id) || $country_id == ""))
        {
            return ["status" => 0, "msg" => "Country is required on Yield Curve Chart"];
        }
        $rules = [
            'post_title.en.*.min'=>'English post title is min 2 character!',
            'post_description.en.*.min'=>'English post description is min 2 character!',
            'post_title.es.*.min'=>'English post title is min 2 character!',
            'post_description.es.*.min'=>'English post description is min 2 character!',
            ];

		$validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:'.TBL_COUNTRY.',id',
			'graph_period' => ['required', Rule::in($months)],
            'graph_type' => ['required', Rule::in(['line','yield_curve', 'relval'])],
            'security_id' => 'exists:'.TBL_SECURITY.',id',
            'option_maturity' => Rule::in(['maturity','duration']),
            'option_price' => Rule::in(['price','yield','spread']),
            'option_rating' => Rule::in([0,1]),
            'option_credit' => Rule::in([5,1]),
            'status' => ['required', Rule::in([1,0])],
            'order' => 'required|min:0|numeric',
            'post_title.en.*' => 'min:2',
            'post_description.en.*' => 'min:2',
			'post_title.es.*' => 'min:2',
			'post_description.es.*' => 'min:2',
        ],$rules);

        // check validations
        if ($validator->fails())
        {
            $messages = $validator->messages();

            $status = 0;
            $msg = "";

            foreach ($messages->all() as $message)
            {
                $msg .= $message . "<br />";
            }
        }
        else
        {
            $security_id = $request->get('security_id');
            $country_id = $request->get('country_id');
            $graph_type = $request->get('graph_type');
            $statuss = $request->get('status');
            $order = $request->get('order');
			$post_title = $request->get('post_title');
			$post_description = $request->get('post_description');
			$graph_period = $request->get('graph_period');
            $option_maturity = $request->get('option_maturity');
			$option_price = $request->get('option_price');
            $option_rating = $request->get('option_rating');
			$option_credit = $request->get('option_credit');

            if($graph_type == 'line' && empty($security_id))
            {
                $status = 0;
                $msg = 'Please enter security !';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(!empty($graph_type) && $graph_type == 'yield_curve' && !empty($security_id))
            {
                $status = 0;
                $msg = "Please don't add graph type <b>Yield Curve</b> with security!";
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(!empty($graph_type) && $graph_type == 'line' && (!empty($option_maturity) || !empty($option_price)))
            {
                $option_maturity = null;
                $option_price = null;
            }
            if($graph_type == 'yield_curve' && empty($option_maturity))
            {
                $status = 0;
                $msg = 'Please select Maturities option !';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if($graph_type == 'yield_curve' && empty($option_price))
            {
                $status = 0;
                $msg = 'Please select Price option !';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(empty($post_title['en'][0]) &&  empty($post_title['es'][0]))
            {
                $status = 0;
                $msg = 'Please enter at least one title!';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(empty($post_description['en'][0]) && empty($post_description['es'][0]))
            {
                $status = 0;
                $msg = 'Please enter at least one description!';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(!empty($order) && $order>0)
            {
                $yes = HomeSlider::where('order',$order)->first();
                if($yes)
                {
                    $status = 0;
                    $msg = 'Order number already exsits!';
                    return ['status' => $status, 'msg' => $msg, 'data' => $data];
                }
            }

            $obj = new HomeSlider();

			$obj->country_id = $country_id;
			$obj->graph_period = $graph_period;
            $obj->graph_type = $graph_type;
            $obj->security_id = $security_id;
            $obj->option_maturity = $option_maturity;
            $obj->option_price = $option_price;
            $obj->status = $statuss;
            $obj->order = $order;
            $obj->option_rating = $option_rating;
            $obj->option_credit = $option_credit;
            $obj->save();

            $languages = \App\Custom::getLanguages();
            foreach ($languages as $locale => $val)
            {

                if(is_array($post_title) && !empty($post_title))
                {
                    $title = isset($post_title[$locale][0]) ? $post_title[$locale][0] : '';
                    $obj->translateOrNew($locale)->title = $title;
                }
                if(is_array($post_description) && !empty($post_description))
                {   
                    $desc = isset($post_description[$locale][0]) ? $post_description[$locale][0] : '';
                    $obj->translateOrNew($locale)->description = $desc;
                }
            }
            $obj->save();

            $id = $obj->id;
            //store logs detail
            $params=array();

            $params['adminuserid']  = \Auth::guard('admins')->id();
            $params['actionid']     = $this->adminAction->ADD_HOME_SLIDER;
            $params['actionvalue']  = $id;
            $params['remark']       = "Add Home Slider::".$id;

            $logs=\App\Models\AdminLog::writeadminlog($params);

            session()->flash('success_message', $msg);

        }

        return ['status' => $status, 'msg' => $msg, 'data' => $data];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_HOME_SLIDER);

        if($checkrights)
        {
            return $checkrights;
        }

        $formObj = $this->modelObj->find($id);

        if(!$formObj)
        {
            abort(404);
        }

        $data = array();
        $data['formObj'] = $formObj;
        $data['page_title'] = "Edit ".$this->module;
        $data['buttonText'] = "Update";
        $data['action_url'] = $this->moduleRouteText.".update";
        $data['action_params'] = $formObj->id;
        $data['method'] = "PUT";
		$data['months'] = getMonths();
        $data['graphs'] = \App\Models\Securities::pluck('security_name','id')->all();
        $data['countries'] = \App\Models\Country::getCountryList();
        $data['orderMax'] = null;
        $data['languages']= \App\Custom::getLanguages();
        $data['graphTypes']= ['line'=>'Line Graph','yield_curve'=>'Yield Curve (Scatter)'];

        $data['maturities']= ['maturity'=>'Maturity','duration'=>'Duration'];
        $data['prices']= ['price'=>'Price','yield'=>'Yield','spread'=>'Spread'];

        return view($this->moduleViewName.'.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$EDIT_HOME_SLIDER);

        if($checkrights)
        {
            return $checkrights;
        }

        $model = $this->modelObj->find($id);

        $status = 1;
        $msg = $this->updateMsg;
        $data = array();
		$months = getMonths();
		$months = array_keys($months);

        $graph_type = $request->get("graph_type");
        $country_id = $request->get("country_id");
        if($graph_type == "yield_curve" && (empty($country_id) || $country_id == ""))
        {
            return ["status" => 0, "msg" => "Country is required on Yield Curve Chart"];
        }
        $rules = [
            'post_title.en.*.min'=>'English post title is min 2 character!',
            'post_description.en.*.min'=>'English post description is min 2 character!',
            'post_title.es.*.min'=>'English post title is min 2 character!',
            'post_description.es.*.min'=>'English post description is min 2 character!',
            ];

		$validator = Validator::make($request->all(), [
            'country_id' => 'exists:'.TBL_COUNTRY.',id',
            'security_id' => 'exists:'.TBL_SECURITY.',id',
            'graph_type' => ['required', Rule::in(['line','yield_curve'])],
			'graph_period' => ['required', Rule::in($months)],
            'option_maturity' => Rule::in(['maturity','duration']),
            'option_price' => Rule::in(['price','yield','spread']),
            'status' => ['required', Rule::in([1,0])],
            'order' => 'required|min:0|numeric',
            'post_title.en.*' => 'min:2',
            'post_description.en.*' => 'min:2',
            'post_title.es.*' => 'min:2',
            'post_description.es.*' => 'min:2',
        ],$rules);

        // check validations
        if(!$model)
        {
            $status = 0;
            $msg = "Record not found !";
        }
        else if ($validator->fails())
        {
            $messages = $validator->messages();

            $status = 0;
            $msg = "";

            foreach ($messages->all() as $message)
            {
                $msg .= $message . "<br />";
            }
        }
        else
        {
			$security_id = $request->get('security_id');
			$country_id = $request->get('country_id');			
            $graph_type = $request->get('graph_type');
			$statuss = $request->get('status');
			$order = $request->get('order');
		    $post_title = $request->get('post_title');
		    $post_description = $request->get('post_description');
			$graph_period = $request->get('graph_period');
            $option_maturity = $request->get('option_maturity');
            $option_price = $request->get('option_price');

            if($graph_type == 'line' && empty($security_id))
            {
                $status = 0;
                $msg = 'Please enter security !';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if($graph_type == 'yield_curve' && empty($option_maturity))
            {
                $status = 0;
                $msg = 'Please select Maturities option !';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(!empty($graph_type) && $graph_type == 'line' && (!empty($option_maturity) || !empty($option_price)))
            {
                $option_maturity = null;
                $option_price = null;
            }
            if($graph_type == 'yield_curve' && empty($option_price))
            {
                $status = 0;
                $msg = 'Please select Price option !';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(!empty($graph_type) && $graph_type == 'yield_curve' && !empty($security_id))
            {
                $status = 0;
                $msg = "Please don't add graph type <b>Yield Curve</b> with security!";
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(empty($post_title['en'][0]) &&  empty($post_title['es'][0]))
            {
                $status = 0;
                $msg = 'Please enter at least one title!';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(empty($post_description['en'][0]) && empty($post_description['es'][0]))
            {
                $status = 0;
                $msg = 'Please enter at least one description!';
                return ['status' => $status, 'msg' => $msg, 'data' => $data];
            }
            if(!empty($order) && $order>0)
            {
                $yes = HomeSlider::where('order',$order)->where('id','!=',$id)->first();
                if($yes)
                {
                    $status = 0;
                    $msg = 'Order number already exsits !';
                    return ['status' => $status, 'msg' => $msg, 'data' => $data];
                }
            }

			$model->country_id = $country_id;
			$model->graph_period = $graph_period;
            $model->graph_type = $graph_type;
            $model->security_id = $security_id;
            $model->status = $statuss;
            $model->order = $order;
            $model->option_maturity = $option_maturity;
            $model->option_price = $option_price;
            $model->save();

            $languages = \App\Custom::getLanguages();
            foreach ($languages as $locale => $val) {

                if(is_array($post_title) && !empty($post_title))
                {
                    $title = isset($post_title[$locale][0]) ? $post_title[$locale][0] : '';
                    $model->translateOrNew($locale)->title = $title;
                }
                if(is_array($post_description) && !empty($post_description))
                {   
                    $desc = isset($post_description[$locale][0]) ? $post_description[$locale][0] : '';
                    $model->translateOrNew($locale)->description = $desc;
                }
            }
            $model->save();

            //store logs detail
            $params=array();

            $params['adminuserid']  = \Auth::guard('admins')->id();
            $params['actionid']     = $this->adminAction->EDIT_HOME_SLIDER;
            $params['actionvalue']  = $id;
            $params['remark']       = "Edit Home Slider::".$id;

            $logs=\App\Models\AdminLog::writeadminlog($params);
        }

        return ['status' => $status,'msg' => $msg, 'data' => $data];
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$DELETE_HOME_SLIDER);

        if($checkrights)
        {
            return $checkrights;
        }

        $modelObj = $this->modelObj->find($id);

        if($modelObj)
        {
            try
            {
                $backUrl = $request->server('HTTP_REFERER');
                $sliders = \App\Models\HomeSliderTranslation::where('home_slider_id',$id);
                if($sliders)
                {
                    $sliders->delete();
                    $modelObj->delete();
                }
                session()->flash('success_message', $this->deleteMsg);

                //store logs detail
                $params=array();

                $params['adminuserid']  = \Auth::guard('admins')->id();
                $params['actionid']     = $this->adminAction->DELETE_HOME_SLIDER;
                $params['actionvalue']  = $id;
                $params['remark']       = "Delete Home Slider::".$id;

                $logs=\App\Models\AdminLog::writeadminlog($params);

                return redirect($backUrl);
            }
            catch (Exception $e)
            {
                session()->flash('error_message', $this->deleteErrorMsg);
                return redirect($this->list_url);
            }
        }
        else
        {
            session()->flash('error_message', "Record not exists");
            return redirect($this->list_url);
        }
    }

    public function data(Request $request)
    {
        $checkrights = \App\Models\Admin::checkPermission(\App\Models\Admin::$LIST_HOME_SLIDER);

        if($checkrights)
        {
            return $checkrights;
        }

        $model = HomeSlider::select(TBL_HOME_SLIDER.".*",TBL_SECURITY.".security_name as graph",\DB::raw("CONCAT(".TBL_COUNTRY.".title,' (',".TBL_COUNTRY.".country_code,')')  AS country"),TBL_HOME_SLIDER_TRANSLATION.".title as en_title")
                ->leftJoin(TBL_SECURITY,TBL_SECURITY.".id","=",TBL_HOME_SLIDER.".security_id")
                ->leftJoin(TBL_COUNTRY,TBL_COUNTRY.".id","=",TBL_HOME_SLIDER.".country_id")
                ->leftJoin(TBL_HOME_SLIDER_TRANSLATION,TBL_HOME_SLIDER_TRANSLATION.".home_slider_id","=",TBL_HOME_SLIDER.".id")
                ->where(TBL_HOME_SLIDER_TRANSLATION.'.locale','en');

        return Datatables::eloquent($model)

            ->addColumn('action', function(HomeSlider $row) {
                return view("admin.partials.action",
                    [
                        'currentRoute' => $this->moduleRouteText,
                        'row' => $row,
                        'isEdit' => \App\Models\Admin::isAccess(\App\Models\Admin::$EDIT_HOME_SLIDER),
                        'isDelete' => \App\Models\Admin::isAccess(\App\Models\Admin::$DELETE_HOME_SLIDER),
                        'isNewsStatus' => \App\Models\Admin::isAccess(\App\Models\Admin::$DELETE_HOME_SLIDER),

                    ]
                    )->render();
            })

            ->editColumn('created_at', function($row){

                if(!empty($row->created_at))

            return date("j M, Y h:i:s A",strtotime($row->created_at));
                else
                    return '-';
            })
            ->editColumn('status', function($row){

                if($row->status == 1)
                    return '<a class="btn btn-success btn-xs">Active</a>';
                else
                    return '<a class="btn btn-warning btn-xs">Inactive</a>';
            })
            
            ->rawColumns(['action','status'])
            ->filter(function ($query)
                {
                    $search_start_date = trim(request()->get("search_start_date"));
                    $search_end_date = trim(request()->get("search_end_date"));
                    $search_graph = request()->get("search_graph");
                    $search_country = request()->get("search_country");
                    $search_status = request()->get("search_status");

                    if (!empty($search_start_date)){

                    $from_date=$search_start_date.' 00:00:00';
                    $convertFromDate= $from_date;

                    $query = $query->where(TBL_HOME_SLIDER.".created_at",">=",addslashes($convertFromDate));
                    }

					if(!empty($search_country))
                    {
                        $query = $query->where(TBL_COUNTRY.".title", 'LIKE', '%'.$search_country.'%');
                    }

                    if (!empty($search_end_date)){

                        $to_date=$search_end_date.' 23:59:59';
                        $convertToDate= $to_date;

                        $query = $query->where(TBL_HOME_SLIDER.".created_at","<=",addslashes($convertToDate));
                    }
                    if(!empty($search_graph))
                    {
                        $query = $query->where(TBL_SECURITY.".security_name", 'LIKE', '%'.$search_graph.'%');
                    }

                    if($search_status == "1" || $search_status == "0")
                    {
                        $query = $query->where(TBL_HOME_SLIDER.".status", $search_status);
                    }

                })
            ->make(true);
    }

    public function getsecurities(Request $request)
    {
        $country_id = $request->get("country_id");
        $arr_security = array();
        if(!empty($country_id)){
            $arr_security = Securities::where('country_id', $country_id)->pluck('security_name','id')->all();
        }
        return $arr_security;
    }
}
