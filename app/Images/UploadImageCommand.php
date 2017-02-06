<?php 

namespace App\Images;

class UploadImageCommand
{
    public $uid;
    public $image;

    function __construct ($uid, $image)
    {
        $this->uid = $uid;
        $this->image = $image;
    }
}
