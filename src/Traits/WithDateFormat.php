<?php

namespace Sosupp\SlimerMetrics\Traits;

use Illuminate\Support\Carbon;

trait WithDateFormat
{
    protected function configureDateAsRange(string|array $date)
    {
        $prepDate = $this->formatDates($date);

        $date = collect($prepDate)->reject(function($prepDate){
            return empty($prepDate) || is_null($prepDate);
        })->toArray();

        // dd($date, $prepDate);
        if(count($date) == 1){
            return [
                'start' => $date['start'] . ' 00:00:00',
                'end' => $date['start'] . ' 23:59:59',
            ];
        }

        return [
            'start' => $date['start'] . ' 00:00:00',
            'end' => $date['end']. ' 23:59:59',
        ];
    }

    private function formatDates($key)
    {
        return [
            'today' => [
                'start' => Carbon::today()->startOfDay()->format('Y-m-d'),
                'start' => Carbon::today()->startOfDay()->format('Y-m-d'),
            ],
            'yesterday' => [
                'start' => Carbon::yesterday()->startOfDay()->format('Y-m-d'),
                'start' => Carbon::yesterday()->startOfDay()->format('Y-m-d'),
            ],
            'this week' => [
                'start' => Carbon::today()->startOfWeek()->format('Y-m-d'),
                'end' => Carbon::today()->endOfWeek()->format('Y-m-d')
            ],
            'last week' => [
                'start' => Carbon::today()->startOfWeek()->subDays(7)->format('Y-m-d'),
                'end' => Carbon::today()->startOfWeek()->subDays(1)->format('Y-m-d')
            ],
            'this month' => [
                'start' => Carbon::today()->startOfMonth()->format('Y-m-d'),
                'end' => Carbon::today()->endOfMonth()->format('Y-m-d')
            ],
            'last month' => [
                'start' => Carbon::today()->startOfMonth()->subMonth()->format('Y-m-d'),
                'end' => Carbon::today()->startOfMonth()->subDays(1)->format('Y-m-d')
            ],
            'this year' => [
                'start' => Carbon::today()->startOfYear()->format('Y-m-d'),
                'end' => Carbon::today()->endOfYear()->format('Y-m-d')
            ],
            'last year' => [
                'start' => Carbon::today()->startOfYear()->subYear()->format('Y-m-d'),
                'end' => Carbon::today()->startOfYear()->subDays(1)->format('Y-m-d')
            ],
            'all time' => null,
            "" => null
        ][$key];
    }
}
