<?php 

namespace App\Images;

use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    public function transform (Image $image)
    {
        $owner = $image->owner;

        return [
        	// 'path'		=>	$image->path,
            'filename'	=>	$image->filename,
            'extension' =>	$image->extension,
            'mime'		=>	$image->mime,
            'size'		=>	$image->size,
            'content'	=>	$image->content,
            'caption'	=>	$image->caption,
            'src'       =>  "/users/" . $owner->id . "/images/" . $image->filename,
        ];
    }
}
