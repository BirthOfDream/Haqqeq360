<?php

namespace App\Repositories\HeaderRepository;

use App\Models\Header;
use Exception;

class HeaderRepository
{
    public function getAll()
    {
        return Header::latest()->get();
    }

    public function create(array $data)
    {
        return Header::create($data);
    }

    public function findBySlug(string $slug)
    {
        return Header::where('slug', $slug)->firstOrFail();
    }
        public function find($idOrSlug)
{
    return Header::where('id', $idOrSlug)
        ->orWhere('slug', $idOrSlug)
        ->firstOrFail();
}
}