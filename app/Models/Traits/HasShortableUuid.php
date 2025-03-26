<?php

namespace App\Models\Traits;

trait HasShortableUuid
{

    /**
     * Get the short id of the model.
     * Shorted id is the first 10 characters of the UUID without dashes.
     *
     * @return string
     */
    protected function shortId(): string
    {
        return str_replace('-', '', substr($this->getKey(), 0, 10));
    }

}
