<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Api as ApiHelper;
use App\Traits\ApiController;
use App\Models\{Shipment,ShipmentImage,TypeShipment,User};
use App\Http\Resources\Data;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NotifySendShip;
use Carbon\Carbon;

class ShipmentController extends Controller
{
    use ApiController;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $resource = ApiHelper::resource();

        try{

            $shipments =  Shipment::with(['type_shipment','user','images'])
                ->orderBy('created_at','DESC')
                ->paginate(10);

            $data  =  new Data($shipments);
            $resource = array_merge($resource, $data->toArray($request));
            ApiHelper::success($resource);
        }catch(\Exception $e){
            ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }

    public function myShipments(Request $request)
    {
        $resource = ApiHelper::resource();

        try{

            $shipments =  Shipment::with(['type_shipment','user','images'])
              ->where('user_id',Auth::user()->id)
              ->orderBy('created_at','DESC')
              ->paginate(10);

            $data  =  new Data($shipments);
            $resource = array_merge($resource, $data->toArray($request));
            ApiHelper::success($resource);
        }catch(\Exception $e){
            ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }

    public function search(Request $request)
    {
        $resource = ApiHelper::resource();

        try{

            $shipments = Shipment::where(function($query)use($request){
                
                if($request->filter_like_user){
                    $query->whereRaw("user_id IN(SELECT id FROM users WHERE (first_name like '%".$request->filter_like_user."%' or last_name like '%".$request->filter_like_user."%' or email like '%".$request->filter_like_user."%') )");
                }
                
                if ($request->to_date && $request->from_date) {
                    $query->whereBetween('created_at',[Carbon::parse($request->from_date),Carbon::parse($request->to_date.' 23:59:00')]);
                }
                
                if($request->type_shipment_id){
                    $query->where('type_shipment_id',$request->type_shipment_id);
                }
            })
            ->with(['type_shipment','user','images'])
            ->orderBy('created_at','DESC')
            ->paginate(10);
             
            $data  =  new Data($shipments);
            $resource = array_merge($resource, $data->toArray($request));
            ApiHelper::success($resource);
        }catch(\Exception $e){
            ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }

    public function searchMyShipments(Request $request)
    {
        $resource = ApiHelper::resource();

        try{

            $shipments = Shipment::where(function($query)use($request){
                
                if ($request->to_date && $request->from_date) {
                  $query->whereBetween('created_at',[Carbon::parse($request->from_date),Carbon::parse($request->to_date.' 23:59:00')]);
                }
                
                if($request->type_shipment_id){
                  $query->where('type_shipment_id',$request->type_shipment_id);
                }
            })
            ->with(['type_shipment','user','images'])
            ->where('user_id',Auth::user()->id)
            ->orderBy('created_at','DESC')
            ->paginate(10);

            $data  =  new Data($shipments);
            $resource = array_merge($resource, $data->toArray($request));
            ApiHelper::success($resource);
        }catch(\Exception $e){
            ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $resource = ApiHelper::resource();

        try{

            $validator= \Validator::make($request->all(),[
                'type_shipment_id' => 'required|numeric|exists:type_shipments,id',
                'latitude'         => 'nullable',
                'longitude'        => 'nullable',
            ]);

            if($validator->fails()){
                ApiHelper::setError($resource, 0, 422, $validator->errors());
                return $this->sendResponse($resource);
            }

            $shipment = new Shipment;
            $shipment->user_id = Auth::user()->id;
            $shipment->latitude = $request->latitude;
            $shipment->longitude = $request->longitude;
            $shipment->type_shipment_id = $request->type_shipment_id;
            $shipment->save();

            $shipment_images = [];

            foreach ($request->file() as $file) {
                
                $shipment_image = new ShipmentImage;
                $shipment_image->path = $this->uploadFile($file,$shipment->id);
                $shipment_image->shipment_id = $shipment->id;
                $shipment_image->save();

                array_push($shipment_images, $shipment_image);
            }

            $type_shipment = TypeShipment::find($shipment->type_shipment_id);
            
            $user = Auth::user();

            $admin = User::where('is_admin',true)->first();
           
            $admin->notify(new NotifySendShip($shipment,$type_shipment,$user,$shipment_images));

            $data  =  new Data($shipment);
            $resource = array_merge($resource, $data->toArray($request));
          ApiHelper::success($resource);
        }catch(\Exception $e){
          ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

     public function uploadFile($file,$shipment_id)
    {   
        $path = null;
        
        if ($file) {
            $path = $file->store(
                'documents/'.$shipment_id
            );
        }
        
        return $path;
    }
}
