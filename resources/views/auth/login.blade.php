<x-guest-layout>
    @section('title', 'Login')

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card mt-4">
                <div class="card-body p-4">
                    <div class="text-center mt-2">
                        <h5 class="text-primary">Welcome Back!</h5>
                        <p class="text-muted">Sign in to continue to {{ config('app.name') }}.</p>
                    </div>

                    <!-- Session Status -->
                    @if (session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <div class="p-2 mt-4">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <!-- Email Address -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Enter email"
                                    required
                                    autofocus
                                    autocomplete="username">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                @if (Route::has('password.request'))
                                <div class="float-end">
                                    <a href="{{ route('password.request') }}" class="text-muted">Forgot password?</a>
                                </div>
                                @endif
                                <label class="form-label" for="password-input">Password</label>
                                <div class="position-relative auth-pass-inputgroup mb-3">
                                    <input type="password"
                                        class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                                        placeholder="Enter password"
                                        id="password-input"
                                        name="password"
                                        required
                                        autocomplete="current-password">
                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                        type="button"
                                        id="password-addon">
                                        <i class="ri-eye-fill align-middle"></i>
                                    </button>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Remember Me -->
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="checkbox"
                                    name="remember"
                                    id="auth-remember-check">
                                <label class="form-check-label" for="auth-remember-check">Remember me</label>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-success w-100" type="submit">Sign In</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if (Route::has('register'))
            <div class="mt-4 text-center">
                <p class="mb-0">Don't have an account?
                    <a href="{{ route('register') }}" class="fw-semibold text-primary text-decoration-underline">Signup</a>
                </p>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        // Password show/hide toggle
        document.getElementById('password-addon')?.addEventListener('click', function() {
            const passwordInput = document.getElementById('password-input');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('ri-eye-fill');
                icon.classList.add('ri-eye-off-fill');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('ri-eye-off-fill');
                icon.classList.add('ri-eye-fill');
            }
        });
    </script>
    @endpush
</x-guest-layout>