<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Api as ApiHelper;
use App\Traits\ApiController;
use App\Models\TypeShipment;
use App\Http\Resources\Data;


class TypeShipmentController extends Controller
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
            $type_shipments = [];

            if ($request->all) {
                $type_shipments =  TypeShipment::all();
            }else if($request->filter_like){
                $type_shipments =  TypeShipment::where('name','LIKE','%'.$request->filter_like.'%')->paginate(10);
            
            }else{
                $type_shipments =  TypeShipment::paginate(10);
            }

          $data  =  new Data($type_shipments);
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
                'name' => 'required|string|unique:type_shipments,name',
                'icon_path' => 'required|file'
             ]);

            if($validator->fails()){
                ApiHelper::setError($resource, 0, 422, $validator->errors());
                return $this->sendResponse($resource);
            }

            $type_shipment = new TypeShipment;
            $type_shipment->icon_path = $this->uploadFile($request->file('icon_path'));
            $type_shipment->name = $request->name;
            $type_shipment->save();

            $data  =  new Data($type_shipment);
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
    public function show(Request $request, $id)
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
        $resource = ApiHelper::resource();

        try{

            $validator= \Validator::make($request->all(),[
                'name' => 'required|string|unique:type_shipments,name,'.$id,
             ]);

            if($validator->fails()){
                ApiHelper::setError($resource, 0, 422, $validator->errors());
                return $this->sendResponse($resource);
            }

            $type_shipment = TypeShipment::where('id',$id)->first();
            
            if ($request->file('icon_path')) {
                $type_shipment->icon_path = $this->uploadFile($request->file('icon_path'));
            }
            
            $type_shipment->name = $request->name;
            $type_shipment->save();

            $data  =  new Data($type_shipment);
            $resource = array_merge($resource, $data->toArray($request));
          ApiHelper::success($resource);
        }catch(\Exception $e){
          ApiHelper::setException($resource, $e);
        }

        return $this->sendResponse($resource);
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

     public function uploadFile($file)
    {   
        $path = null;
        
        if ($file) {
            $path = $file->store(
                'documents/'
            );
        }
        
        return $path;
    }
}
