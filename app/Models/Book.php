<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Author;

class Book extends Model
{
    use HasFactory;

    protected $table = "books";
    protected $fillable = [
        "id",
        "isbn",
        "title",
        "description",
        "publish_date",
        "category_id",
        "editorial_id",
    ];

    function category()
    {
        return $this->belongsTo(Category::class,  "category_id");
    }

    function editorial()
    {
        return $this->belongsTo(Editorial::class, "editorial_id");
    }

    public $timestamps = false;

    public function authors(){
        return $this->belongsToMany(
            Author::class, // Table relationship
            'authors_books', // Table intersection
            'books_id', // from
            'authors_id' // to
        );
    }
}
