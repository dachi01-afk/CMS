<?php

namespace App\View\Components\mycomponents;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class card extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        // Atribut wajib
        public string $title,
        public string $value,
        public string $percentage,
        public string $context,
        public string $iconSvg,
        public bool $isPositive,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.mycomponents.card');
    }
}
