<?php

namespace App\Http\Controllers\Trendyol;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeCategory;
use App\Models\AttributeValue;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CategoryController extends Controller
{
    //add sub categories rrecursively
    public function addSubCategories($categories,$parent_id=null){
        foreach($categories as $category){
            $category['parent_id']=$parent_id;
            $category["displayName"]=$category["name"];
            $category['trendyol_id']=$category['id'];
            $category['id']=Category::create($category)->id;
            if(isset($category['subCategories'])){
                $this->addSubCategories($category['subCategories'],$category['id']);
            }
        }
    }
    public function  getCategories(Request $request){
        $res=Http::get('https://api.trendyol.com/sapigw/product-categories');
        $categories=$res->json()['categories'];
        $this->addSubCategories($categories);

    }
    //getcategories attributes
    public function getAttributes(Request $request){
        try{
        $categories=Category::where("id",">","729")->get();

             $i=0;
                foreach ($categories as $category) {

                    $res = Http::timeout(500)->get('https://api.trendyol.com/sapigw/product-categories/' . $category->trendyol_id . '/attributes');
                    if(  $attributes = $res->json()['categoryAttributes']){
                        foreach ($attributes as $attribute) {
                            $attributeData = [
                                'category_id' => $category->id,
                                'trendyol_id' => $attribute['attribute']['id'],
                                'name' => $attribute['attribute']['name'],
                                'required' => $attribute['required'] == true ? "1" : "0",
                                'varianter' => $attribute['varianter'] == true ? "1" : "0",
                                'slicer' => $attribute['slicer'] == true ? "1" : "0"
                            ];

                            // Update or create the attribute
                            $attributeModel = Attribute::updateOrCreate([
                                'trendyol_id' => $attribute['attribute']['id']
                            ], $attributeData);
                            //add attributeCategories
                            $attribute_category=[
                                'category_id'=>$category->id,
                                'attribute_id'=>$attributeModel->id,

                            ];
                            $categoryAttribute=AttributeCategory::create($attribute_category);



                            // Handle attribute values
                            if (isset($attribute['attributeValues'])) {
                                foreach ($attribute['attributeValues'] as $attributeValue) {
                                    $attributeValueData = [
                                        'attribute_id' => $attributeModel->id,
                                        'trendyol_id' => $attributeValue['id'],
                                        'name' => $attributeValue['name'], // assuming there is a 'name' field
                                        // Add other fields as necessary
                                    ];

                                    // Update or create the attribute value
                                    AttributeValue::updateOrCreate(
                                        ['trendyol_id' => $attributeValue['id']],
                                        $attributeValueData
                                    );
                                }
                            }
                        }
                    }else{





                    }



                    $i++;
                    if($i%500==0){
                        sleep(60);
                    }
                }
        }catch (\Exception $e){
            dd($e);
        }
    }
    //add attributes to category
    public function addAttributesToCategory(Request $request){
        $categories=Category::all();
        $i=0;
        foreach ($categories as $category) {
            $res = Http::timeout(500)->get('https://api.trendyol.com/sapigw/product-categories/' . $category->trendyol_id . '/attributes');
            $attributes = $res->json()['categoryAttributes'];

            foreach ($attributes as $attribute) {
                $getAttribute=Attribute::where('trendyol_id',$attribute['attribute']['id'])->first();
                $attribute_category=[
                    'category_id'=>$category->id,
                    'attribute_id'=>$getAttribute['id'],

                ];
                $categoryAttribute=AttributeCategory::create($attribute_category);



            }
            $i++;
            if($i%500==0){
                sleep(60);
            }

        }
    }

    //get products
    public function getProducts(Request $request){

            $res=Http::withToken('d1p1bmczbThCZjZzNWZuWXJmVzY6RTVrNzJJYnRyQjd6U2JVUTdEaTU=')->get('https://api.trendyol.com/sapigw/suppliers/229537/products');
            $products=$res->json()['content'];
            dd($products);

    }




}