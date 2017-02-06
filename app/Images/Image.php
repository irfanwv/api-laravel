<?php 

namespace App\Images;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletingTrait;

class Image extends Eloquent
{
    use SoftDeletingTrait;

    protected $table = "images";

    protected $guarded = ["id"];

    public function owner()
    {
        return $this->morphTo();
    }
}
