<x-guest-layout>
    @section('title', 'Register')

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card mt-4">
                <div class="card-body p-4">
                    <div class="text-center mt-2">
                        <h5 class="text-primary">Create New Account</h5>
                        <p class="text-muted">Get your free {{ config('app.name') }} account now</p>
                    </div>
                    <div class="p-2 mt-4">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <!-- Name -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Enter name"
                                    required
                                    autofocus
                                    autocomplete="name">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Enter email address"
                                    required
                                    autocomplete="username">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                                <div class="position-relative auth-pass-inputgroup">
                                    <input type="password"
                                        class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                                        id="password"
                                        name="password"
                                        placeholder="Enter password"
                                        required
                                        autocomplete="new-password">
                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                        type="button">
                                        <i class="ri-eye-fill align-middle"></i>
                                    </button>
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="mb-3">
                                <label class="form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                <div class="position-relative auth-pass-inputgroup">
                                    <input type="password"
                                        class="form-control pe-5 password-input"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        placeholder="Confirm password"
                                        required
                                        autocomplete="new-password">
                                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon"
                                        type="button">
                                        <i class="ri-eye-fill align-middle"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="mb-0 fs-12 text-muted fst-italic">
                                    By registering you agree to the {{ config('app.name') }}
                                    <a href="#" class="text-primary text-decoration-underline fst-normal fw-medium">Terms of Use</a>
                                </p>
                            </div>

                            <div class="mt-4">
                                <button class="btn btn-success w-100" type="submit">Sign Up</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <p class="mb-0">Already have an account?
                    <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">Signin</a>
                </p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Password show/hide toggle for all password fields
        document.querySelectorAll('.password-addon').forEach(function(button) {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('.password-input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('ri-eye-fill');
                    icon.classList.add('ri-eye-off-fill');
                } else {
                    input.type = 'password';
                    icon.classList.remove('ri-eye-off-fill');
                    icon.classList.add('ri-eye-fill');
                }
            });
        });
    </script>
    @endpush
</x-guest-layout>