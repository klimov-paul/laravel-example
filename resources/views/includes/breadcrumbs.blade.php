<?php
/**
 * @var $breadcrumbs array
 *
 * Breadcrumbs definition Example
 *
 * ```blade
 * @breadcrumbs([
 *     'Foo' => route('foo'),
 *     'Bar' => route('bar'),
 *     'No link'
 * ])
 * ```
 *
 * Use following to render generated breadcrumbs HTML
 *
 * ```blade
 * @yield('breadcrumbs')
 * ```
 */
?>
@if (!empty($breadcrumbs))
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            @foreach ($breadcrumbs as $label => $link)
                @if (is_int($label) && ! is_int($link))
                    <li class="breadcrumb-item active" aria-current="page">{{ $link }}</li>
                @else
                    <li class="breadcrumb-item"><a href="{{ $link }}">{{ $label }}</a></li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif
