<?php

namespace Sosupp\SlimerMetrics\Engine;

use Illuminate\Support\Facades\DB;
use Sosupp\SlimerMetrics\Contracts\MetricRegistryInterface;

class MetricQueryBuilder
{
    protected $registry;

    public function __construct(MetricRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function build($metric, $date, $groupBy = null)
    {
        // dd($groupBy, $date);

        $definition = $this->registry->get($metric);

        $definition = $definition->toArray();

        $query = DB::table($definition['table'])
        ->whereBetween('created_at', $date);

        if(isset($definition['where'])) {
            foreach ($definition['where'] as $column => $value) {
                $query->where($column, $value);
            }
        }

        if($definition['type'] === 'sum') {
            $query->selectRaw("SUM({$definition['column']}) as value");
        }

        if($definition['type'] === 'count') {
            $query->selectRaw("COUNT(*) as value");
        }

        if($groupBy) {
            $query->selectRaw("$groupBy AS bucket")
            ->groupBy('bucket')
            ->orderBy('bucket');
        }

        return $query;
    }
}
