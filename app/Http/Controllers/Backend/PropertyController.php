<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\MultiImage;
use App\Models\Facility;
use App\Models\Amenities;
use App\Models\PropertyType;
use App\Models\User;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PropertyController extends Controller
{
    public function AllProperty(){        
        
        $property = Property::latest()->get();
        return view('backend.property.all_property',compact('property'));

    }//End Method


    public function AddProperty(){        
        
        $propertyType = PropertyType::latest()->get();
        $amenities = Amenities::latest()->get();
        $activeAgent = User::where('status','active')->where('role','agent')->
            latest()->get();

        return view('backend.property.add_property', compact('propertyType',
            'amenities','activeAgent'));

    }//End Method

    public function StoreProperty(Request $request){

        // create image manager with desired driver
        $manager = new ImageManager(new Driver());

        $amen = $request->amenities_id;
        $amenities = implode(',',$amen);
        // dd($amenities);

        $pcode = IdGenerator::generate(['table'=>'properties','field'=>'property_code','length'=>5,'prefix'=>'PC']);

        $image = $request->file('property_thumbnail');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        $img = $manager->read($image)->resize(370,250);
        // $img->save('upload/property/thumbnail/'.$name_gen);
        $save_url= 'upload/property/thumbnail/'.$name_gen;

        $property_id = Property::insertGetId([

            'ptype_id' => $request->ptype_id,
            'amenities_id' => $amenities,
            'property_name' => $request->property_name,
            'property_slug' => strtolower(str_replace(' ','-',$request->property_slug)),
            'property_code' => $pcode,
            'property_status' => $request->property_status,

            'lowest_price' => $request->lowest_price,
            'max_price' => $request->max_price,
            'short_descp' => $request->short_descp,
            'long_descp' => $request->long_descp,
            'bedrooms' => $request->bedrooms,
            'bathrooms' => $request->bathrooms,
            'garage' => $request->garage,
            'garage_size' => $request->garage_size,

            'property_size' => $request->property_size,
            'property_video' => $request->property_video,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,

            'neighborhood' => $request->neighborhood,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'featured' => $request->featured,
            'hot' => $request->hot,
            'agent_id' => $request->agent_id,
            'status' => 1,
            'property_thumbnail' => $save_url,
            'created_at' => Carbon::now(),

        ]);

        //*********Multiple Image Upload From Here*********//

        $images = $request->file('multi_img');
        foreach($images as $img){

        $make_name = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        $imgs = $manager->read($img)->resize(770,520);
        // $imgs->save('upload/property/multi-image/'.$make_name);
        $uploadPath= 'upload/property/multi-image/'.$make_name;

        MultiImage::insert([

            'property_id' => $property_id,
            'photo_name' => $uploadPath,
            'created_at' => Carbon::now(),

        ]);

        }//End ForEach

        //*********END Multiple Image Upload From Here*********//

        //*********Facilities Add From Here*********//

        $facilities = Count($request->facility_name);

        if($facilities!=Null){
            for($i=0; $i<$facilities ; $i++){
                $fcount = new Facility();
                $fcount->property_id = $property_id;
                $fcount->facility_name = $request->facility_name[$i];
                $fcount->distance = $request->distance[$i];
                $fcount->save();
            }
        }

        //*********END Facilities Add From Here*********//

        $notification = array(
            'message' => 'Property Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.property')->with($notification);

    }//End Method

}
    