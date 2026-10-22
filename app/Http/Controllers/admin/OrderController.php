<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Car;
use App\Models\CostOfTrip;
use App\Models\User;
use App\Models\Order;
use App\Events\OrderSent;
use App\Events\OrderStatusChanged; // добавлены события статусов RAY
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with('tariffClass')->orderBy('id','desc')->paginate(30);

        return view('admin.order.index',compact('orders'));
    }

    public function indexAjax()
    {
        $orders = Order::with('tariffClass')->orderBy('id','desc')->paginate(30);

        return view('admin.order.table', compact('orders'));
    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role','driving')->get();
        $tariffs = CostOfTrip::all();
        return view('admin.order.add',compact('users','tariffs'));
        // return view('admin.order.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // dd($request);
        // $order = Order::find(244);

        // dd($order);
        
        // поменяла тут метод RAY
        // $order = Order::create($request->all());
        // if($order){
        //     if($order->driver_id){
        //         event(new OrderSent($order,$order->driver_id));
        //     }
        //     else{
        //         event(new OrderSent($order, role: 'driving'));
        //     }
        //     return redirect(route('order.index'));
        // }else{
        //     return back();
        // }


        // Новый медот RAY
            $data = $request->all();
            if (!empty($data['driver_id'])) {
                $data['status'] = Order::STATUS_SEARCHING;
                }
            $order = Order::create($data);
    if ($order) {
        if ($order->driver_id) {
            event(new OrderSent($order, $order->driver_id));
            event(new OrderStatusChanged($order, 'assigned_by_dispatcher'));
        } else {
            event(new OrderSent($order, role: 'driving'));
        }

        return redirect(route('order.index'));
    }

    return back();

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $users = User::where('role','driving')->get();
        $tariffs = CostOfTrip::all();
        $order = Order::find($id);

        return view('admin.order.edit',compact('users','order','tariffs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // dd($request->all());

        // меняю этот метод RAY
        // $order = Order::find($id)->update($request->all());
        // $order = Order::find($id);
        // if($order){

        //     if($order->driver_id){
        //         event(new OrderSent($order,$order->driver_id));
        //     }
        //     else{
        //         event(new OrderSent($order, role: 'driving'));
               
        //     }

        //     return redirect(route('order.index'));
        // }else{
        //     return back();
        // }

        //на вот этот метод RAY

            $order = Order::findOrFail($id);
    $oldDriver = $order->driver_id;

    $order->update($request->all());

    // Если водитель изменился и он назначен — переводим в searching
    if ($order->driver_id && $order->driver_id !== $oldDriver) {
        $order->update(['status' => Order::STATUS_SEARCHING]);
        event(new OrderSent($order, $order->driver_id));
        event(new OrderStatusChanged($order, 'reassigned_by_dispatcher'));
    } elseif (!$order->driver_id) {
        event(new OrderSent($order, role: 'driving'));
    }

    return redirect(route('order.index'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $car = Order::find($id);
        if($car->delete()){
            return 'true';
        }
        else return 'false';
    }
}
