<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource as BaseJsonResource;

/**
 * @template TModel of Model
 * @property TModel $resource
 */
abstract class JsonResource extends BaseJsonResource {}
