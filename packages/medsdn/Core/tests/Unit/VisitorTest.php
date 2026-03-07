<?php

use Illuminate\Http\Request;
use Webkul\Core\Visitor;

it('can be instantiated with the project visitor configuration', function () {
    $request = Request::create('/');

    $visitor = new Visitor($request, config('visitor'));

    expect($visitor)->toBeInstanceOf(Visitor::class);
});
