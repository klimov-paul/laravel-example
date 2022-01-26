<footer class="bd-footer py-5 mt-5 bg-light">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3 mb-3">
                <a class="d-inline-flex align-items-center mb-2 link-dark text-decoration-none" href="{{ route('home') }}">
                    <span class="fs-5">{{ config('app.name') }}</span>
                </a>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-2">Designed and built for common <a href="https://laravel.com/docs" target="_blank" rel="external">Laravel</a> programming approaches demonstration.</li>
                    <li class="mb-2">Code licensed <a href="#" target="_blank" rel="license noopener">BSD</a>.</li>
                    <li class="mb-2">Created by <a href="https://github.com/klimov-paul" target="_blank" rel="external">Paul Klimov</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2 offset-lg-1 mb-3">
                <h5>Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('home') }}">Home</a></li>
                    <li class="mb-2"><a href="{{ route('rents.index') }}">Rents</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2 offset-lg-1 mb-3">
                <h5>Guides</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="https://github.com/klimov-paul/laravel-example/blob/master/docs/README.md" target="_blank" rel="external">Education Documentation</a></li>
                    <li class="mb-2"><a href="https://laravel.com/docs" target="_blank" rel="external">Laravel Documentation</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2 offset-lg-1 mb-3">
                <h5>Community</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="https://github.com/klimov-paul/laravel-example" target="_blank" rel="external">Source Code</a></li>
                    <li class="mb-2"><a href="https://www.patreon.com/klimov_paul" target="_blank" rel="external">Sponsorship</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>
