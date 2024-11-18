<style>
    .active {
        font-weight: bold; /* Aktif butonu kalın yap */
        color: #38bdf8; /* Tailwind'deki cyan-400 rengi */
        border-bottom: 2px solid #38bdf8; /* Alt çizgi ekleyerek vurgula */
    }
</style>

<nav class="bg-gray-800 p-4 flex items-center justify-between shadow-lg">
    <div class="flex space-x-6">
        <a href="/" class="flex items-center text-cyan-500 hover:text-cyan-400 @if (request()->is('/')) active @endif">
            <i class="fa-solid fa-chart-simple"></i>&nbsp;
            Dashboard
        </a>
        <a href="/survey" class="flex items-center text-cyan-500 hover:text-cyan-400 @if (request()->is('survey')) active @endif">
            <i class="fa-solid fa-plus"></i>&nbsp;
            Create Survey
        </a>
        <a href="/list" class="flex items-center text-cyan-500 hover:text-cyan-400 @if (request()->is('list')) active @endif">
            <i class="fa-solid fa-file-circle-check"></i>&nbsp;
            Surveys
        </a>
    </div>

    <div class="relative flex items-center space-x-4">
        <div class="flex items-center space-x-2 cursor-pointer">
            <span class="text-gray-300">{{ auth()->user()->name }}</span>
            <div class="relative w-11 h-11">
                <div class="absolute inset-0 bg-[rgb(6,182,212)] rounded-full"></div>
                <img src="/laravel_files/profile_picture.png" alt="Profile" class="relative w-11 h-11 rounded-full">
            </div>
        </div>

        <div class="absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg hidden" id="profile-dropdown">
            <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Profile</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Settings</a>
            <a href="/logout" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Log Out</a>
        </div>
    </div>
</nav>

<div id="toast" class="hidden fixed top-5 right-5 max-w-xs p-4 bg-green-500 text-white rounded-lg shadow-lg">
    <p id="toast-message">Başarıyla kaydedildi!</p>
    <button onclick="closeToast()" class="absolute top-0 right-0 m-2 text-white">×</button>
</div>

<script>
    // Dropdown functionality
    const profileDropdown = document.getElementById('profile-dropdown');
    const profileIcon = document.querySelector('.cursor-pointer');

    profileIcon.addEventListener('click', () => {
        profileDropdown.classList.toggle('hidden');
    });

    window.addEventListener('click', (e) => {
        if (!profileIcon.contains(e.target)) {
            profileDropdown.classList.add('hidden');
        }
    });

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toast-message');

        toastMessage.innerText = message;

        if (type === 'error') {
            toast.classList.remove('bg-green-500');
            toast.classList.add('bg-red-500');
        } else {
            toast.classList.remove('bg-red-500');
            toast.classList.add('bg-green-500');
        }

        toast.classList.remove('hidden');

        setTimeout(() => {
            toast.classList.add('hidden');
        }, 3000);
    }

    function closeToast() {
        const toast = document.getElementById('toast');
        toast.classList.add('hidden');
    }
</script>
