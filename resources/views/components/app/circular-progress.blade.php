<div class="relative size-8">
  <svg class="size-full -rotate-90" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg">
    <!-- Background Circle -->
    <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-gray-200 dark:text-neutral-700" stroke-width="3"></circle>
    <!-- Progress Circle -->
    <circle cx="18" cy="18" r="16" fill="none" class="stroke-current text-blue-600 dark:text-blue-500" stroke-width="3" stroke-dasharray="100" stroke-dashoffset="{{ 100 - $perc }}" stroke-linecap="round"></circle>
  </svg>

  <!-- Percentage Text -->
  <div class="absolute top-1/2 start-1/2 transform -translate-y-1/2 -translate-x-1/2 flex items-center justify-center">
    <span class="text-center text-[9px] font-bold text-blue-600 dark:text-blue-500">{{ $perc }}%</span>
  </div>
</div>