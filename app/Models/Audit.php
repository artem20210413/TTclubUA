<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit as BaseAudit;

class Audit extends BaseAudit
{
    /**
     * {@inheritdoc}
     */
    public function setOldValuesAttribute(array $values): void
    {
        $this->attributes['old_values'] = empty($values)
            ? null
            : json_encode($values, JSON_UNESCAPED_UNICODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setNewValuesAttribute(array $values): void
    {
        $this->attributes['new_values'] = empty($values)
            ? null
            : json_encode($values, JSON_UNESCAPED_UNICODE);
    }
}
