<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MatterType;
use Auth;


class MatterTypeController extends Controller
{
    
    public function __construct()
    {       
        $this->middleware('auth');
    }

    public function index()
    {
        Auth::user()->authorizeRoles('Administrator');
        return view("matter_type.index");
    }

    public function show($mt_id)
    {
        Auth::user()->authorizeRoles('Administrator');
        return view("matter_type.show");
    }
    
    public function create()
    {
        Auth::user()->authorizeRoles('Administrator');
        return view("matter_type.create");
    }
    
    public function store() 
    {        
        Auth::user()->authorizeRoles('Administrator');
        $matter_type_params =    array(
                                'title'   => request('title'),
                            );
        
        $matter_type = new MatterType();
        $response = $matter_type->saveMatterType($matter_type_params);
        
        return redirect('/matter_type')->with($response['success'], $response['message']);
    }

    public function destroy($mt_id)
    {
        Auth::user()->authorizeRoles('Administrator');
        $matter_type = new MatterType();
        $response = $matter_type->deleteMatter($mt_id);
        
        return redirect('/matter_type')->with($response['success'], $response['message']);
    }

    public function list()
    {
        $matter_type = new MatterType();

        $result = $matter_type->getAllMatterTypes();

        return array('data' => $result);
    }
    
}
