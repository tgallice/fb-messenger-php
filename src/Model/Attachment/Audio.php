<?php

namespace Tgallice\FBMessenger\Model\Attachment;

use Tgallice\FBMessenger\Model\Attachment;

class Audio extends File
{
    public function __construct($filePath)
    {
        parent::__construct($filePath, Attachment::TYPE_AUDIO);
    }
}
