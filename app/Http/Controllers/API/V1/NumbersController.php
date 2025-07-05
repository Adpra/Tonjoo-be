<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NumbersController extends Controller
{
    public function fibonacci($n = 5){
        $fibos = [];
        for ($i = 0; $i <= $n; $i++) {
            if($i == 0 ){
                $fibos[$i] = 0;
            }else if($i == 1){
                $fibos[$i] = 1;
            }else{
                $fibos[$i] = $fibos[$i - 1] + $fibos[$i - 2];
            }
        }

        return [
            'result' => $fibos[$n],
        ];
    }

    public function fibonacciProduct($n1, $n2)
    {
        if (!is_numeric($n1) || $n1 < 0 || !is_numeric($n2) || $n2 < 0) {
            return response()->json(['error' => 'Input harus berupa angka >= 0'], Response::HTTP_BAD_REQUEST)
                ->header('Access-Control-Allow-Origin', '*');
        }

        $fib1 = $this->fibonacci($n1)['result'];
        $fib2 = $this->fibonacci($n2)['result'];
        $sum = $fib1 + $fib2;

        return response()->json([
            'fibonacci_n1' => $fib1,
            'fibonacci_n2' => $fib2,
            'sum' => $sum,
        ]);
    }
}
