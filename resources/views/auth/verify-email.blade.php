<x-guest-layout>
    @section('title', 'Verify Email')

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card mt-4">
                <div class="card-body p-4 text-center">
                    <div class="mb-4">
                        <div class="avatar-lg mx-auto">
                            <div class="avatar-title bg-light text-primary display-5 rounded-circle">
                                <i class="ri-mail-line"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <h4>Verify Your Email</h4>
                        <p class="text-muted mx-4">
                            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
                        </p>

                        @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success" role="alert">
                            A new verification link has been sent to the email address you provided during registration.
                        </div>
                        @endif

                        <div class="mt-4">
                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    Resend Verification Email
                                </button>
                            </form>

                            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                                @csrf
                                <button type="submit" class="btn btn-link text-muted">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>