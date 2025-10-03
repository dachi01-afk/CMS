<?php

namespace App\View\Components\mycomponents;

use Closure;
use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Request;

class sidebar_link extends Component
{
    public $href;
    public $active;

    public function __construct($href)
    {
        $this->href = $href;
        // Logika untuk menentukan apakah link aktif
        // Request::routeIs() akan mengecek apakah route saat ini cocok dengan $href
        $this->active = Request::routeIs($href);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.mycomponents.sidebar_link');
    }
}
