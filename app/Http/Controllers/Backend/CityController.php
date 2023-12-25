<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PropertyType;
use App\Models\City;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CityController extends Controller
{
    public function AllCity(){

        $city = City::latest()->get();
        return view('backend.city.all_city',compact('city'));

    } // End Method


    public function AddCity(){
        return view('backend.city.add_city');
    } // End Method 


    public function StoreCity(Request $request){

    // create image manager with desired driver
    $manager = new ImageManager(new Driver());

        $image = $request->file('city_image');
        $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
        $img = $manager->read($image)->resize(370,275);
        $encoded = $img->toJpg();
        // save encoded image
        $encoded->save('upload/city/'.$name_gen);
        $save_url= 'upload/city/'.$name_gen;

        City::insert([
            'city_name' => $request->city_name,
            'city_image' => $save_url, 
        ]);

        $notification = array(
            'message' => 'City Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.city')->with($notification);

    }// End Method

    
    public function EditCity($id){

        $city = City::findOrFail($id);
        return view('backend.city.edit_city',compact('city'));

    }// End Method

    public function UpdateCity(Request $request){

        $manager = new ImageManager(new Driver());

        $city_id = $request->id;

        if ($request->file('city_image')) {
            $image = $request->file('city_image');
            $name_gen = hexdec(uniqid()).'.'.$image->getClientOriginalExtension();
            $img = $manager->read($image)->resize(370,275);
            $encoded = $img->toJpg();
            // save encoded image
            $encoded->save('upload/city/'.$name_gen);
            $save_url= 'upload/city/'.$name_gen;

    City::findOrFail($city_id)->update([
        'city_name' => $request->city_name,
        'city_image' => $save_url, 
    ]);

     $notification = array(
            'message' => 'City Updated with Image Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.city')->with($notification);

        }else{

       City::findOrFail($city_id)->update([
        'city_name' => $request->city_name, 
    ]);

     $notification = array(
            'message' => 'City Updated without Image Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.city')->with($notification);

        }

    }// End Method 


    public function DeleteCity($id){

        $city = City::findOrFail($id);
        $img = $city->city_image;
        unlink($img);

        City::findOrFail($id)->delete();

         $notification = array(
            'message' => 'City Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification); 

    }// End Method

}
