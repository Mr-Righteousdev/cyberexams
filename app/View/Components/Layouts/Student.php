<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class Student extends Component
{
    public function render(): View
    {
        return view('components.layouts.student');
    }
}
