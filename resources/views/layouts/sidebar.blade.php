<!-- Sidebar Navigation -->
<aside @click="sidebarOpen = true"
    class="hidden w-64 flex-col bg-gradient-to-b from-indigo-900 via-indigo-800 to-indigo-900 text-white shadow-xl md:flex">
    <x-sidebar />
</aside>

<!-- Mobile Menu Button & Overlay -->
<div class="pointer-events-none fixed inset-0 z-20 md:hidden" :class="{ 'pointer-events-auto': mobileMenuOpen }">
    <div @click="mobileMenuOpen = false" :class="{ 'bg-black bg-opacity-50': mobileMenuOpen }"
        class="absolute inset-0 transition-opacity duration-300"></div>
</div>
