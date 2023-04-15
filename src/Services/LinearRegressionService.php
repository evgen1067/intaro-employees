<?php

namespace App\Services;

class LinearRegressionService
{
    private array $xTrain;

    private float $xAvg;

    private array $yTrain;

    private float $yAvg;

    private float $a;

    private float $b;

    public function __construct(array $xTrain, array $yTrain)
    {
        $this->xTrain = $xTrain;
        $this->yTrain = $yTrain;

        $this->xAvg = $this->avgInArray($xTrain);
        $this->yAvg = $this->avgInArray($yTrain);

        $this->coefficientsRegression();
    }

    public function predict($x)
    {
        return max($this->a + $this->b * $x, 0);
    }

    private function avgInArray(array $array): float
    {
        $avg = 0.0;
        foreach ($array as $item) {
            $avg += $item / count($array);
        }

        return $avg;
    }

    private function coefficientsRegression(): void
    {
        $numerator = 0.0;
        $denominator = 0.0;

        for ($i = 0, $iMax = count($this->xTrain); $i < $iMax; ++$i) {
            $numerator += ($this->xTrain[$i] - $this->xAvg) * ($this->yTrain[$i] - $this->yAvg);
            $denominator += ($this->xTrain[$i] - $this->xAvg) * ($this->xTrain[$i] - $this->xAvg);
        }

        $this->b = round(fdiv($numerator, $denominator), 6);
        $this->a = $this->yAvg - $this->b * $this->xAvg;
    }
}