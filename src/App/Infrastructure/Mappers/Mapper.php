<?php

namespace AlanGiacomin\LaravelCqrs\App\Infrastructure\Mappers;

use AlanGiacomin\LaravelCqrs\App\Domain\Entities\DomainItem;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @template TDomain of DomainItem
 */
abstract class Mapper
{
    /**
     * @param  TModel  $model
     * @return TDomain
     *
     * @codeCoverageIgnore
     */
    abstract public static function toDomain(Model $model): DomainItem;

    /**
     * @param  TDomain  $domain
     * @return TModel
     *
     * @codeCoverageIgnore
     */
    abstract public static function toPersistence(DomainItem $domain): Model;
}
