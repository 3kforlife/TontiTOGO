<template>
  <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Background circle - modern with border -->
    <circle cx="20" cy="20" r="18" fill="#F0FDF4" stroke="#22C55E" stroke-width="1.5" />
    
    <!-- Connecting lines (hands holding) -->
    <path d="M20 13 L27 20" stroke="#22C55E" stroke-width="1.5" stroke-linecap="round" />
    <path d="M27 20 L20 27" stroke="#EAB308" stroke-width="1.5" stroke-linecap="round" />
    <path d="M20 27 L13 20" stroke="#EF4444" stroke-width="1.5" stroke-linecap="round" />
    <path d="M13 20 L20 13" stroke="#16A34A" stroke-width="1.5" stroke-linecap="round" />
    
    <!-- Person 1 - Top (Green - primary) -->
    <g transform="translate(20, 7)">
      <circle cx="0" cy="0" r="3.5" fill="#22C55E" />
      <path d="M-3.5 6.5 Q0 4.5 3.5 6.5 L3.5 13 Q0 15 -3.5 13 Z" fill="#22C55E" />
    </g>
    
    <!-- Person 2 - Right (Yellow - secondary) -->
    <g transform="translate(33, 20)">
      <circle cx="0" cy="0" r="3.5" fill="#EAB308" />
      <path d="M-3.5 6.5 Q0 4.5 3.5 6.5 L3.5 13 Q0 15 -3.5 13 Z" fill="#EAB308" transform="rotate(90 0 0)" />
    </g>
    
    <!-- Person 3 - Bottom (Red - accent) -->
    <g transform="translate(20, 33)">
      <circle cx="0" cy="0" r="3.5" fill="#EF4444" />
      <path d="M-3.5 6.5 Q0 4.5 3.5 6.5 L3.5 13 Q0 15 -3.5 13 Z" fill="#EF4444" transform="rotate(180 0 0)" />
    </g>
    
    <!-- Person 4 - Left (Green - primary) -->
    <g transform="translate(7, 20)">
      <circle cx="0" cy="0" r="3.5" fill="#16A34A" />
      <path d="M-3.5 6.5 Q0 4.5 3.5 6.5 L3.5 13 Q0 15 -3.5 13 Z" fill="#16A34A" transform="rotate(270 0 0)" />
    </g>
    
    <!-- Center circle (caisse commune) -->
    <circle cx="20" cy="20" r="5.5" fill="white" stroke="#22C55E" stroke-width="2" />
    <circle cx="20" cy="20" r="2.5" fill="#22C55E" />
  </svg>
</template>

<script setup>
// Ce composant est réutilisable pour le logo
</script>
