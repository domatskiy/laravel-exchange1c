<?php

namespace Domatskiy\Exchange1C\Events;

use \Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class ImportComplate extends Event
{
    use SerializesModels;

    const TYPE_CATALOG = 'catalog';
    const TYPE_ORDER = 'order';

    public $type;
    public $dir;
    public $file;

    public function __construct($type, $dir, $file)
    {
        $this->type = $type;
        $this->dir = $dir;
        $this->file = $file;
    }
}