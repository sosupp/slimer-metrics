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

    public function build($metric, $groupBy = null)
    {
        $definition = $this->registry->get($metric);

        $definition = $definition->toArray();

        $query = DB::table($definition['table']);

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
            $query->groupBy($groupBy);
        }

        return $query;
    }
}
