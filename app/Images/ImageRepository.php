<?php 

namespace App\Images;

use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;

use App\Users\User;
use App\Users\UserRepository;

use App\Images\Image;
use Intervention\Image\Facades\Image as Intervention;

class ImageRepository
{
    private $model;

    public function __construct (Image $model)
    {
        $this->model = $model;
    }

    public function create ($params)
    {
        return $this->model->fill ($params);
    }

    public function save (Image $image)
    {
        return $image->save();
    }

    public function upload (User $user, $file, $content = "default", $caption = "")
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $filename = pathinfo($file->getClientOriginalName())["filename"];
        $extension = $file->getClientOriginalExtension();
        $mime = finfo_file($finfo, $file->getRealPath());
        $size = filesize($file->getRealPath());
        
        finfo_close($finfo);
        
        $path = storage_path("images/$user->id/");

        $file->move(storage_path("images/$user->id/"), "$filename.$extension");

        // it says it can take the straight symfony object but it lies
        $file = file_get_contents(storage_path("images/$user->id/$filename.$extension"));
        // make some thumbs
        Intervention::make ($file)
            ->resize (96, null, function($c) { $c->aspectRatio(); })
            ->save (storage_path("images/$user->id/" . $filename."_thumb." . $extension));

        Intervention::make ($file)
            ->resize (150, null, function($c) { $c->aspectRatio(); })
            ->save (storage_path("images/$user->id/" . $filename."_sm." . $extension));

        Intervention::make ($file)
            ->resize (300, null, function($c) { $c->aspectRatio(); })
            ->save (storage_path("images/$user->id/" . $filename."_md." . $extension));

        Intervention::make ($file)
            ->resize (500, null, function($c) { $c->aspectRatio(); })
            ->save (storage_path("images/$user->id/" . $filename."_lg." . $extension));

        Intervention::make ($file)
            ->resize (640, null, function($c) { $c->aspectRatio(); })
            ->save (storage_path("images/$user->id/" . $filename."_xl." . $extension));

        $image = $this->create(compact("path", "filename", "extension", "mime", "size", "content", "caption"));

        $this->save ($image);

        return $image;
    }
}
