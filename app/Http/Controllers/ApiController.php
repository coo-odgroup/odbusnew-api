<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cities;
use App\Constants\HttpStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;


class ApiController extends Controller
{
     public function getCities(Request $request){

         $status = true;
         $statusCode = config('constants.HTTP_OK');
         $message = '';
         $response = [];

         try{
            //   $cacheKey = 'cities:v1:active';
            //   $ttl      = 60 * 60 * 24; // 24 hours

            //  // 1️⃣ Try Redis First
            //  $response = Cache::get($cacheKey);
            //  if (!$response) {

                $cities = Cities::with(['state', 'district','synonyms'])
                                     ->where('active_status', 1)
                                     ->get();

                if($cities->isEmpty()){
                    $status = false;
                    $message = config('constants.RECORD_NOT_FOUND');
                } else {

                        $response = $cities->map(function ($city) {

                            $baseName = $city->city_name;
                            $extras   = [];

                            if ($city->district) {
                                $extras[] = ucwords(strtolower($city->district->district_name));
                            }

                            if ($city->state) {
                                $extras[] = ucwords(strtolower($city->state->state_name));
                            }

                            $formattedName = $baseName;

                            if (!empty($extras)) {
                                $formattedName .= ' ( ' . implode(', ', $extras) . ' )';
                            }

                            return [
                                'id'            => $city->id,
                                'cityName'      => $formattedName,
                                'alias'         => $city->alias,
                                'synonyms'      => $city->synonyms->pluck('synonym'),
                            ];
                        });

                     // 3️⃣ Store in Redis
                   //  Cache::put($cacheKey, $response, $ttl);
                     $message = config('constants.RECORD_FETCHED');
                }
            // }

         } catch (\Exception $e){

             Log::error('GetLocation API Error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
         
             $status = false;
             $statusCode = config('constants.HTTP_INTERNAL_SERVER_ERROR', 500);
             $message = config('constants.SERVER_ERROR_MESSAGE');
         }

         return response()->json([
             'status' => $status,
             'statusCode' => $statusCode,
             'message' => $message,
             'response' => $response
         ], $statusCode);


     }
}
