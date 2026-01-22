<x-guest-layout>
    @section('title', 'Forgot Password')

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card mt-4">
                <div class="card-body p-4">
                    <div class="text-center mt-2">
                        <h5 class="text-primary">Forgot Password?</h5>
                        <p class="text-muted">Reset password with {{ config('app.name') }}</p>

                        <lord-icon src="https://cdn.lordicon.com/rhvddzym.json"
                            trigger="loop"
                            colors="primary:#0ab39c"
                            class="avatar-xl">
                        </lord-icon>
                    </div>

                    <div class="alert border-0 alert-warning text-center mb-2 mx-2" role="alert">
                        Enter your email and instructions will be sent to you!
                    </div>

                    <div class="p-2">
                        @if (session('status'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label">Email</label>
                                <input type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="Enter Email"
                                    required
                                    autofocus>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-center mt-4">
                                <button class="btn btn-success w-100" type="submit">Send Reset Link</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <p class="mb-0">Wait, I remember my password...
                    <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">Click here</a>
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>