<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

    }

    public function view(Request $request)
    {
        $class = 'data';

        $mainCats = [
            'healthcare',
            'innovation',
            'education',
            'public_sector',
            'municipalities',
            'agriculture',
            'justice',
            'economy_business',
        ];

        $filter = $request->offsetGet('filter');

        return view('data/list', compact('class','mainCats', 'filter'));
    }

    public function relatedData() {

    }

    public function reportedList() {

    }

    public function reportedView() {

    }

}
