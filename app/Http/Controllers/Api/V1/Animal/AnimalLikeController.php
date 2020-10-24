<?php

namespace App\Http\Controllers\Api\V1\Animal;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AnimalLikeController extends Controller
{

    public function __construct()
    {
        /**
         * 一定要加上中介層auth，必須要登入才可以使用。
         * 另外一方面，沒有加入中介層下方auth()無法讀取到會員登入資訊
         */
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Animal $animal)
    {
        return $animal->likes()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Animal $animal)
    {
        $result = $animal->likes()->toggle(auth()->user()->id);

        return response($result, Response::HTTP_OK);
    }
}
