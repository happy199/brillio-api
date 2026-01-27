<div id="toast-container" class="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

<script>
    window.showToast = function (message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        // Create toast element
        const toast = document.createElement('div');
        // Style : Dark styling as requested "un toast rapide est suffisant", similar to standard toast
        // Using common tailwind classes
        toast.className = `transform transition-all duration-300 translate-y-2 opacity-0 bg-gray-900/90 backdrop-blur text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-3 pointer-events-auto min-w-[250px] max-w-sm border border-gray-700/50`;

        // Icon based on type
        let icon = '';
        if (type === 'success') {
            icon = `<div class="bg-green-500/20 p-1 rounded-full"><svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>`;
        } else if (type === 'error') {
            icon = `<div class="bg-red-500/20 p-1 rounded-full"><svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div>`;
        } else {
            icon = `<div class="bg-blue-500/20 p-1 rounded-full"><svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>`;
        }

        toast.innerHTML = `${icon}<span class="font-medium text-sm text-gray-100">${message}</span>`;

        container.appendChild(toast);

        // Animate in
        // Use timeout to ensure flow
        setTimeout(() => {
            toast.classList.remove('translate-y-2', 'opacity-0');
        }, 10);

        // Remove after 3s
        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }
</script>