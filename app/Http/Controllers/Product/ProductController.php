<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeCategory;
use App\Models\AttributeValue;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    //list variants
    public function listVariants(Request $request)
    {
        if($request->company_id==null){
            return response()->json(['message' => 'Company ID is required']);
        }else{
            $res=Http::withBasicAuth(env("API_USERNAME"),env("API_PASSWORD"))-> get(env('API_URL').'/product_variations/2');
            return response()->json(['message' => 'Variant List',"response"=>$res->json()]);
        }

    }


    //add features
    public function createFeature(Request $request, $id=null){

        $limit = 10;
        $offset = 0;
        try {
            while (true) {
                $attributes = Attribute::where("id",">",248)->skip($offset)
                    ->take($limit)
                    ->get();

                if ($attributes->isEmpty()) {
                    break; // Eğer veri kalmadıysa döngüden çık
                }


                foreach ($attributes as $attribute) {
                    $categoryString="";


                            $attributeCategories=AttributeCategory::where("attribute_id",$attribute->id)->get();



                            foreach($attributeCategories as $category){

                            $c=Category::where("id",$category->category_id)->first();
                                $categoryString.=$c->cscart_id.",";
                            }





                   // dd($attribute->id);
                    $requestArray = [];
                    $requestArray['description'] = $attribute->name;
                    $requestArray['name'] = $attribute->name;
                    $requestArray['variants'] = [];
                    $requestArray['feature_type'] = "M";
                    $requestArray['categories_path'] = $categoryString;
                    $requestArray['company_id'] = 1;



                        $attributeValues = AttributeValue::where('attribute_id', $attribute->id)->get();
                        foreach ($attributeValues as $val) {

                            $requestArray['variants'][] = [
                                "variant" => $val->name,
                            ];
                        }



                  //  dd($requestArray);
                  try {
                   // dd($requestArray);
                    $response = Http::withBasicAuth(env("API_USERNAME"), env("API_PASSWORD"))
                    ->timeout(500) // Zaman aşımı süresini 60 saniye olarak ayarla
                    ->post(env('API_URL') . '/features', $requestArray);

                  } catch (\Throwable $th) {
                   dd($th);
                  }

                        if ($response->successful()) {
                            // İstek başarılı olursa yapılacak işlemler
                            echo $response->body();
                        } else {
                            // İstek başarısız olursa yapılacak işlemler
                            echo 'Request failed: ' . $response->status();
                        }

                }

                $offset += $limit; // Bir sonraki chunk için offset'i arttır
            }

        } catch (\Throwable $th) {
            dd($th->getMessage());
        }

      //  dd($requestArray);



        return response()->json(['message' => 'Feature Created']);
    }
    //create category
    public function createCategory()
    {
        $allCategory=Category::whereNull("cscart_id")->get();
        $arr=[];
        foreach ($allCategory as $category) {

            $parent_id=$category->parent_id?$category->parent_id:0;


                $parent_cscart_id=Category::where("id",$parent_id)->first();

            $res=Http::timeout(500)->withBasicAuth(env("API_USERNAME"),env("API_PASSWORD"))->post(env('API_URL').'/categories',[
                "company_id"=>0,
                "description"=>$category->name,
                "category"=>$category->name,
                "status"=>"A",
                 "parent_id"=>$parent_cscart_id?$parent_cscart_id->cscart_id:0,
                "trendyol_id"=>"0",
                "storefront_id"=>1
         ]);
       //  dd($res->json());
      $cscart_category_id=$res->json()['category_id'];
      //update category
    $updateCategory=Category::where("id",$category->id)->update(["cscart_id"=>$cscart_category_id]);
        }

        return response()->json(['message' => 'Category Created',"response"=>$res->json()]);


    }

    //list
    public function index(Request $request)

    {
        if($request->company_id==null){
            return response()->json(['message' => 'Company ID is required']);
        }else{
            //convert trendyol api response
            // "page": 0,
            // "size": 50,
            // "totalElements": 161,
            // "totalPages": 4,
            // "content": [
            //     {
            //         "approved": true,
            //         "archived": false,
            //         "attributes": [
            //             {
            //                 "attributeId": 47,
            //                 "attributeName": "Renk",
            //                 "attributeValue": "Krem"
            //             },
            //             {
            //                 "attributeId": 348,
            //                 "attributeName": "Web Color",
            //                 "attributeValue": "Bej",
            //                 "attributeValueId": 6997
            //             },
            //             {
            //                 "attributeId": 343,
            //                 "attributeName": "Cinsiyet",
            //                 "attributeValue": "Unisex",
            //                 "attributeValueId": 4296
            //             },
            //             {
            //                 "attributeId": 14,
            //                 "attributeName": "Materyal",
            //                 "attributeValue": "Ahşap",
            //                 "attributeValueId": 3986
            //             },
            //             {
            //                 "attributeId": 92,
            //                 "attributeName": "Boyut/Ebat",
            //                 "attributeValue": "34 x 24",
            //                 "attributeValueId": 23788
            //             },
            //             {
            //                 "attributeId": 338,
            //                 "attributeName": "Beden",
            //                 "attributeValue": "Tek Ebat",
            //                 "attributeValueId": 6821
            //             }
            //         ],
            //         "barcode": "110krm",
            //         "brand": "Hediye Mola",
            //         "brandId": 1016190,
            //         "categoryName": "Çerçeve",
            //         "createDateTime": 1622933441000,
            //         "description": "EBAT:21x29.7 CM; ÇERÇEVE KALINLIĞI 2 CMDİR; ÇERÇEVENİN ÖN KISMINDA CAM DEĞİL ŞEFFAF PVC VARDIR; ŞEFFAF PVC ÖZELLİĞİ HAFİF VE KIRILMAZ OLMASIDIR; RENK SEÇENEKLERİ MEVCUTTUR; YATAY VE DİKEY OLARAK ASILABİLECEK APARATLARI MEVCUTTUR;",
            //         "dimensionalWeight": 0,
            //         "gender": "Unisex",
            //         "hasActiveCampaign": false,
            //         "id": "20e83777c91fce5eac6f78bd3be5fd5c",
            //         "images": [
            //             {
            //                 "url": "https://cdn.dsmcdn.com/ty125/product/media/images/20210606/1/96240352/183439754/0/0_org_zoom.jpg"
            //             },
            //             {
            //                 "url": "https://cdn.dsmcdn.com/ty124/product/media/images/20210606/1/96240352/183439754/1/1_org_zoom.jpg"
            //             },
            //             {
            //                 "url": "https://cdn.dsmcdn.com/ty124/product/media/images/20210606/1/96240352/183439754/2/2_org_zoom.jpg"
            //             }
            //         ],
            //         "lastUpdateDate": 1688985999000,
            //         "listPrice": 349.99,
            //         "locked": false,
            //         "onSale": false,
            //         "pimCategoryId": 461,
            //         "platformListingId": "1d79942db9fd480b3e1b5133a0577171",
            //         "productCode": 183439754,
            //         "productContentId": 112827380,
            //         "productMainId": "110 belge",
            //         "quantity": 0,
            //         "salePrice": 299.99,
            //         "stockCode": "",
            //         "stockUnitType": "Adet",
            //         "supplierId": 229537,
            //         "title": "5 Li Set A4(21x29.7) Belge Çerçeve Fotoğraf Çerçeve",
            //         "vatRate": 20,
            //         "rejected": false,
            //         "rejectReasonDetails": [],
            //         "blacklisted": false,
            //         "hasHtmlContent": false,
            //         "productUrl": "https://www.trendyol.com/hediye-mola/5-li-set-a4-21x29-7-belge-c-erc-eve-fotog-raf-c-erc-eve-p-112827380?merchantId=229537&filterOverPriceListings=false"
            //     }



            $res=Http::withBasicAuth(env("API_USERNAME"),env("API_PASSWORD"))-> get(env('API_URL').'/stores/'.$request->company_id.'/products');
            $products=$res->json()['products'];
           // dd($products);

            $params=$res->json()['params'];
             $myResponse=[
                 "page"=>$params["page"],
                 "size"=>$params["items_per_page"],
                 "totalElements"=>$params["total_items"],
                 "totalPages"=>$params["page"],
                 "content"=>[]
             ];
             //get company detail
                $companyBrand="";
                $company=Http::withBasicAuth(env("API_USERNAME"),env("API_PASSWORD"))-> get(env('API_URL').'/vendors/'.$request->company_id);
                $companyDetail=$company->json();
                $companyBrand=$companyDetail['company'];
            $myProduct=[];
            foreach($products as $product){
               $category_name= $this->getCategoriesDetail($product["main_category"]);
               $attributes=[];
               if(count($product['product_features'])>0){
                foreach($product['product_features'] as $key=>$val){
                    $attributes[]=[
                         "attributeId"=>$val['feature_id'],
                         "attributeName"=>$val['internal_name'],
                         "attributeValue"=>$val['variant'],
                         "attributeValueId"=>$val['variant_id']
                    ];
              }
               }

              // $attributes=
                //dd($product);
                $myProduct[]=[
                    "approved"=>$product["status"]=="A"?true:false,
                    "archived"=>$product["status"]=="H"?true:false,
                    "attributes"=>$attributes,
                    "barcode"=>$product["product_code"],
                    "brand"=>$companyBrand,
                    "brandId"=>$product["company_id"],
                    "categoryName"=>$category_name['category'],
                    "createDateTime"=>$product["timestamp"],
                    "description"=>$product["product"],
                    "dimensionalWeight"=>$product["weight"],
                ];
            }

            return response()->json(['message' => 'Product List',"response"=>$res->json()]);
        }

    }


    public function listAttribute(){
        $attributeCategories=AttributeCategory::where("attribute_id",74)->get();
        $i=0;
        foreach($attributeCategories as $key){
           // dd($key);
           $i++;
            $category=Category::where("id",$key->category_id)->first();
            print_r($category->name);
            print_r($category->id);
            echo "<br>";
        }

        print_r("Toplam ".$i." adet kategori bulundu");



    }


    //get categories :id
    public function getCategoriesDetail($id)
    {
        $res=Http::withBasicAuth(env("API_USERNAME"),env("API_PASSWORD"))-> get(env('API_URL').'/categories/'.$id);
        return $res->json();
    }

}