<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\User;
use App\Order;
use App\Exports\UsersExport;
use Illuminate\Support\Facades\Hash;
use DB;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $sort_search = null;
        $customers = Customer::orderBy('created_at', 'desc');
        // dd($customers);
        
        if ($request->has('search')){
            $sort_search = $request->search;
            $user_ids = User::where('user_type', 'customer')->where(function($user) use ($sort_search){
                $user->where('name', 'like', '%'.$sort_search.'%')->orWhere('email', 'like', '%'.$sort_search.'%');
            })->pluck('id')->toArray();
            $customers = $customers->where(function($customer) use ($user_ids){
                $customer->whereIn('user_id', $user_ids);
            });
        }
        
        $customers = $customers->paginate(15);
        // dd($customers);
        
        return view('customers.index', compact('customers', 'sort_search'));
    }

    function excel()
    {
   
     return Excel::download(new UsersExport, 'users.xlsx');
    //  $customers = Customer::orderBy('created_at', 'desc')->get()->toArray();
    //  $customer_array[] = array('Name', 'Address', 'City', 'Postal Code', 'Country');
    //  foreach($customers as $customer)
    //  {
      
    //   $customer_array[] = array(
    //    'Customer Name'  => $customer['id'],
    //   );
    //  }
  
    //  Excel::download($customer_array)->download('xlsx');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
       $customer = Customer::findOrFail(decrypt($id));
       
      return view('customers.edit', compact('customer'));

    }



    public function update(Customer $customer, Request $request)
    {
        // dd('dfdf');
        $user = $customer->user;

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->mobile = $request->mobile;
        if(strlen($request->password) > 0){
            $user->password = Hash::make($request->password);
        }
        if($user->save()){
            if($user->update()){
                flash(__('Customer has been updated successfully'))->success();
                return redirect()->route('customers.index');
            }
        }

        flash(__('Something went wrong'))->error();
        return back();
        // $user->update();

        // return redirect()->route('customers.index');  
      }

    public function destroy($id)
    {
        Order::where('user_id', Customer::findOrFail($id)->user->id)->delete();
        User::destroy(Customer::findOrFail($id)->user->id);
        if(Customer::destroy($id)){
            flash(__('Customer has been deleted successfully'))->success();
            return redirect()->route('customers.index');
        }

        flash(__('Something went wrong'))->error();
        return back();
    }
}
