<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey App</title>
    <script src="/tailwind/3.4.15"></script>
    <link rel="icon" href="/favicon.png">
</head>
<body class="min-h-screen bg-gray-900 flex items-center justify-center">
    <div class="w-full max-w-md p-8 space-y-6 bg-gray-800 rounded-lg shadow-lg">
        <h1 id="form-title" class="text-3xl font-bold text-center text-cyan-500">Login</h1>
        <form id="auth-form" class="space-y-6" method="POST" action="{{ route('login') }}">
            @csrf
            <div id="name-field" class="hidden">
                <label for="name" class="block text-sm font-medium text-gray-300">Name</label>
                <input
                    type="text"
                    id="name"
                    class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent @error('name') border-red-400 shadow-[0_0_0_2px_rgba(248,113,113,0.5)]" @enderror"
                    onfocusout="handleInput(this)"
                    placeholder="Enter your name"
                    maxlength="50"
                    name="name"
                />
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                <input
                    type="email"
                    id="email"
                    class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent @error('email') border-red-400 shadow-[0_0_0_2px_rgba(248,113,113,0.5)]" @enderror"
                    onfocusout="handleInput(this)"
                    placeholder="Enter your email"
                    maxlength="254"
                    name="email"
                    required
                />
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <input
                    type="password"
                    id="password"
                    class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent @error('password') border-red-400 shadow-[0_0_0_2px_rgba(248,113,113,0.5)]" @enderror"
                    onfocusout="handleInput(this)"
                    placeholder="Enter your password"
                    name="password"
                    required
                />
            </div>
            <div id="confirm-password-field" class="hidden">
                <label for="confirm-password" class="block text-sm font-medium text-gray-300">Confirm Password</label>
                <input
                    type="password"
                    id="confirm-password"
                    class="w-full px-4 py-2 mt-1 bg-gray-700 text-gray-100 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                    onfocusout="handleInput(this)"
                    placeholder="Confirm your password"
                    name="password_confirmation"
                />
            </div>
            <button
                id="form-submit"
                type="submit"
                class="w-full px-4 py-2 font-bold text-gray-900 bg-cyan-500 rounded-lg hover:bg-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                Sign In
            </button>
        </form>
        <p class="text-sm text-center text-gray-400">
            <span id="form-toggle-text">Don't have an account?</span>
            <button id="form-toggle" class="text-cyan-500 hover:underline">Sign up</button>
        </p>
    </div>

    <script>
        // Elements
        const form = document.getElementById('auth-form');
        const formTitle = document.getElementById('form-title');
        const formSubmit = document.getElementById('form-submit');
        const formToggle = document.getElementById('form-toggle');
        const formToggleText = document.getElementById('form-toggle-text');
        const nameField = document.getElementById('name-field');
        const confirmPasswordField = document.getElementById('confirm-password-field');

        // State
        let isSignUp = false;
        let isSubmitting = false;

        // Toggle Form Mode
        formToggle.addEventListener('click', () => {
            isSignUp = !isSignUp;

            if (isSignUp) {
                // Switch to Sign Up Mode
                form.action = '{{ route('register') }}';
                formTitle.textContent = 'Sign Up';
                formSubmit.textContent = 'Sign Up';
                formToggleText.textContent = 'Already have an account?';
                formToggle.textContent = 'Sign in';
                nameField.classList.remove('hidden');
                nameField.setAttribute('required', '');
                confirmPasswordField.classList.remove('hidden');
                confirmPasswordField.setAttribute('required', '');
            } else {
                // Switch to Login Mode
                form.action = '{{ route('login') }}';
                formTitle.textContent = 'Login';
                formSubmit.textContent = 'Sign In';
                formToggleText.textContent = "Don't have an account?";
                formToggle.textContent = 'Sign up';
                nameField.classList.add('hidden');
                nameField.removeAttribute('required');
                confirmPasswordField.classList.add('hidden');
                confirmPasswordField.removeAttribute('required');
            }
        });

        function handleInput(input){
            if (input.value.length > 0 && input.value.length <= parseInt(input.getAttribute('maxlength'))) {
                input.classList.remove('border-red-400', 'shadow-[0_0_0_2px_rgba(248,113,113,0.5)]');
            } else {
                input.classList.add('border-red-400', 'shadow-[0_0_0_2px_rgba(248,113,113,0.5)]');
            }

        }
    </script>
</body>
</html>

