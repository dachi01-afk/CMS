<?php

namespace App\View\Components\mycomponent;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class card_patient_summary extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $title,
        public string $totalValue,
        public string $totalLabel, // Pasien
        public string $connectionStatus, // Tidak Terhubung BPJS
        public array $legendData,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.mycomponent.card_patient_summary');
    }
}
