<?php 

namespace App\Images;

use Laracasts\Commander\CommandHandler;
use Laracasts\Commander\Events\DispatchableTrait;
use Log;

class UploadImageCommandHandler implements CommandHandler
{
    use DispatchableTrait;

    /**
     * @var ImageRepository
     */
    protected $imageRepository;


    public function __construct (ImageRepository $imageRepository)
    {
        $this->imageRepository = $imageRepository;
    }

    /**
     * Handle the command
     *
     * @param $command
     * @return mixed
     */
    public function handle($command)
    {

        $image = $this->imageRepository->upload($command->uid, $command->image);

        return $image;
    }
}
