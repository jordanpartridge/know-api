<?php

namespace App\Http\Controllers;

use App\Http\Resources\TagResource;
use App\Models\Tag;

class TagController
{
    public function __invoke(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return TagResource::collection(Tag::all());
    }
}
